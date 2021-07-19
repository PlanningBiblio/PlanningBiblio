<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
    protected $entityManager;

    private $templateParams = array();

    protected $dispatcher;

    private $config = array();

    private $logger;

    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $request = $requestStack->getCurrentRequest();

        $this->entityManager = $GLOBALS['entityManager'];

        $this->templateParams = $GLOBALS['templates_params'];

        $this->dispatcher = $GLOBALS['dispatcher'];

        $this->config = $GLOBALS['config'];

        $this->logger = $logger;
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

    /**
     * @Route("/index.php", name="default", methods={"GET"})
     */
    protected function default_route()
    {
      // Named route used to redirect to old index.php
    }

}
