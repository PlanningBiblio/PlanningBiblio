<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends Controller
{
    protected $entityManager;

    private $templateParams = array();

    protected $dispatcher;

    private $config = array();

    public function __construct()
    {
        $this->entityManager = $GLOBALS['entityManager'];

        $this->templateParams = $GLOBALS['templates_params'];

        $this->dispatcher = $GLOBALS['dispatcher'];

        $this->config = $GLOBALS['config'];
    }

    protected function templateParams( array $params = array() )
    {
        if ( empty($params) ) {
            return $this->templateParams;
        }

        $this->templateParams = array_merge($params, $this->templateParams);

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
}
