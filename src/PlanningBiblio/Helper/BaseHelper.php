<?php

namespace App\PlanningBiblio\Helper;

require_once(__DIR__ . '/../../../public/include/config.php');
require_once(__DIR__ . '/../../../init/init_entitymanager.php');
require_once(__DIR__ . '/../../../init/init_plugins.php');

class BaseHelper
{
    protected $entityManager;

    protected $config;

    protected $dispatcher;

    public function __construct()
    {
        $this->entityManager = $GLOBALS['entityManager'];

        $this->dispatcher = $GLOBALS['dispatcher'] ?? null;

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
