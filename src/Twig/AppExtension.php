<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/include/feries.php');

class AppExtension extends AbstractExtension
{

   public function getFilters()
    {
        return [
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('config', [$this, 'getConfig']),
        ];
    }

    public function getConfig($key = null)
    {
        $config = $GLOBALS['config'];

        // Request all config parameters.
        if ( !isset($key) ) {
            //filter some paremeters for safety.
            unset($config['CAS-CACert']);
            unset($config['LDAP-Password']);
            unset($config['Mail-Password']);
            unset($config['Mail-Signature']);
            unset($config['Planning-AppelDispoMessage']);

            foreach ($config as $key => $value) {
                if (is_array($value)) {
                    unset($config[$key]);
                }
            }

            return $config;
        }

        if ( !isset($config[$key]) ) {
            return null;
        }

        return $config[$key];
    }
}