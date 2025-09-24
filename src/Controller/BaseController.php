<?php

namespace App\Controller;

use App\Entity\ConfigParam;
use App\PlanningBiblio\Notifier;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class BaseController extends AbstractController
{
    protected $entityManager;

    private $templateParams = array();

    protected $dispatcher;

    private $config = array();

    protected \Psr\Log\LoggerInterface $logger;

    protected $notifier;

    protected $permissions;

    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        if (!empty($_ENV['MEMORY_LIMIT'])) {
            ini_set('memory_limit', $_ENV['MEMORY_LIMIT']);
        }

        $request = $requestStack->getCurrentRequest();

        $this->entityManager = $GLOBALS['entityManager'];

        $this->templateParams = $GLOBALS['templates_params'];

        $this->dispatcher = $GLOBALS['dispatcher'];

        $this->logger = $logger;

        $this->permissions = $GLOBALS['droits'];

        $url = $this->entityManager->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => 'URL'])
            ->getValue();

        $GLOBALS['config']['URL'] = $url;
        $this->config = $GLOBALS['config'];
    }

    public function setNotifier(Notifier $notifier): void {
        $this->notifier = $notifier;
    }

    protected function templateParams( array $params = array() )
    {
        if ( empty($params) ) {
            return $this->templateParams;
        }

        foreach ($params as $key => $value) {
            $this->templateParams[$key] = $value;
        }

        return $this;
    }

    protected function output($templateName)
    {
        return $this->render($templateName, $this->templateParams);
    }

    protected function config($key, $value = null)
    {
        if ( !isset($key) ) {
            return null;
        }

        if ( isset($value) ) {
            $this->config[$key] = $value;
            return null;
        }

        if ( !isset($this->config[$key]) ) {
            return null;
        }

        return $this->config[$key];
    }

    protected function csrf_protection(Request $request): bool
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            $session = $request->getSession();
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return false;
        }
        return true;
    }

    protected function returnError($error, $module = 'Planno', $status = 200): \Symfony\Component\HttpFoundation\Response
    {
        $this->logger->error($module . ':' . $error);
        $response = new Response();
        $response->setContent(json_encode(array('error' => $error)));
        $response->setStatusCode($status);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

}
