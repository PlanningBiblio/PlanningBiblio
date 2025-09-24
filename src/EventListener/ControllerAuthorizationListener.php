<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Yaml\Yaml;

use App\Entity\Agent;
use App\Entity\Access;

use ReflectionClass;

class ControllerAuthorizationListener
{
    private array $anonymous_pages = array('', '/index', '/week', '/help');
    private Array $droits;
    private EntityManagerInterface $entityManager;
    private $permissions = array();
    private $templateParams = array();
    private \Twig\ENvironment $twig;

    public function __construct(\Twig\Environment $twig, EntityManagerInterface $em)
    {
        $this->permissions = Yaml::parseFile(__DIR__."/../../config/permissions.yaml");

        $this->twig = $twig;

        $this->templateParams = $GLOBALS['templates_params'];
        $this->droits = $GLOBALS['droits'];
        $this->entityManager = $em;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $page = $event->getRequest()->getPathInfo();
        $page = preg_replace('/([a-z-\/]*).*/', "$1", $page);
        $page = rtrim($page, '/add');
        $page = rtrim($page, '/');

        if (in_array($page, ['/login', '/logout', '/legal-notices', '/ical'])) {
            return;
        }

        if (substr($page, 0, 12) === '/unsubscribe') {
            return;
        }

        // Droits necessaires pour consulter la page en cours
        $loginId = $event->getRequest()->getSession()->get('loginId');
        $accesses = $this->entityManager->getRepository(Access::class)->findBy(array('page' => $page));
        $logged_in = $this->entityManager->find(Agent::class, $loginId);

        $route = $event->getRequest()->attributes->get('_route');

        if ($_SESSION['oups']["Auth-Mode"] == 'Anonyme' ) {
            if (in_array($page, $this->anonymous_pages)) {
                return;
            }
            $this->triggerAccessDenied($event);
            return;
        }

        if(!$logged_in){
            $this->triggerAccessDenied($event);
            return;
        }

        if(empty($this->permissions[$route])){
            if (!$logged_in->can_access($accesses)){
                $this->triggerAccessDenied($event);
            }
            return;
        }

        if (!$this->canAccess($route)) {
            $this->triggerAccessDenied($event);
        }
    }

    private function canAccess($route): bool
    {
        if (!isset($this->permissions[$route])) {
            return true;
        }

        $accesses = $this->permissions[$route];

        $multisites = $GLOBALS['config']['Multisites-nombre'];

        // Right 21 (Edit personnel) gives right 4 (Show personnel)
        if (in_array(21, $this->droits)) {
            $this->droits[] = 4;
        }

        foreach ($accesses as $access) {
            if (in_array($access, $this->droits)) {
                return true;
            }
        }

        // Multisites rights associated with page access
        $multisites_rights = array(201,301);
        if ($multisites > 1 && in_array($accesses[0], $multisites_rights)) {
            for ($i = 1; $i <= $multisites; $i++) {
                $droit = $accesses[0] -1 + $i;
                if (in_array($droit, $this->droits)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function triggerAccessDenied(RequestEvent $event): void{

        $body = $this->twig->render('access-denied.html.twig', $this->templateParams);

        $response = new Response();
        $response->setContent($body);
        $response->setStatusCode(403);

        $event->setResponse($response);
    }
}
