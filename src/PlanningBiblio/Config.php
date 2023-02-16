<?php

namespace App\PlanningBiblio;

class Config
{
    private static $_instance = null;

    private $config_params;

    private function __construct() {
        $this->config_params = $GLOBALS['config'];
    }

    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Config();
        }

        return self::$_instance;
   }

    public function get($key) {
        if (!isset($key) ) {
            return null;
        }

        if (!isset($this->config_params[$key]) ) {
            return null;
        }

        return $this->config_params[$key];
    }
}
