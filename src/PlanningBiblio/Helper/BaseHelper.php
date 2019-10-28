<?php

namespace App\PlanningBiblio\Helper;

class BaseHelper
{
    protected $entityManager;

    protected $config;

    protected $dispatcher;

    public function __construct()
    {
        $this->entityManager = $GLOBALS['entityManager'];

        $this->dispatcher = $GLOBALS['dispatcher'];

        $this->config = $GLOBALS['config'];
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
