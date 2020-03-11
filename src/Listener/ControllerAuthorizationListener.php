<?php

namespace App\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use ReflectionClass;

class ControllerAuthorizationListener
{

    private $templateParams = array();

    private $permissions = array(
        'ajax.editabsencereasons' => array(100),
        'ajax.holidaydelete' => array(100),
        'ajax.changepassword' => array(21),
        'statedweek.availables' => array(1401),
        'statedweek.add' => array(1401),
        'statedweekjob.add' => array(1401),
        'statedweekpause.add' => array(1401),
        'statedweekjob.update' => array(1401),
        'statedweek.remove' => array(1401),
        'statedweekjob.remove' => array(1401),
        'statedweekpause.remove' => array(1401),
        'statedweek.lock' => array(1401),
        'statedweek.empty' => array(1401),
        'statedweek.template.load' => array(1401),
        'statedweek.template.add' => array(1401),
    );

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;

        $this->templateParams = $GLOBALS['templates_params'];
        $this->droits = $GLOBALS['droits'];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');

        if (!$this->canAccess($route)) {
            $body = $this->twig->render('accesss-denied.html.twig', $this->templateParams);

            $response = new Response();
            $response->setContent($body);
            $response->setStatusCode(403);

            $event->setResponse($response);
        }

    }

    private function canAccess($route)
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
        if ($multisites > 1) {
            if (in_array($accesses[0], $multisites_rights)) {
                for ($i = 1; $i <= $multisites; $i++) {
                    $droit = $accesses[0] -1 + $i;
                    if (in_array($droit, $this->droits)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
