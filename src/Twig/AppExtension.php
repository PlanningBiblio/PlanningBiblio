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
            new TwigFilter('datefull', [$this, 'dateFull']),
            new TwigFilter('hour', [$this, 'hour']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('config', [$this, 'getConfig']),
            new TwigFunction('siteName', [$this, 'siteName']),
            new TwigFunction('userCan', [$this, 'userCan']),
        ];
    }

    public function dateFull($date)
    {
        return dateAlpha($date);
    }

    public function hour($hour)
    {
        return heure4($hour);
    }

    public function userCan($right, $site = 0)
    {
        $droits = $GLOBALS['droits'];

        return in_array( $right + $site, $droits );
    }

    public function siteName($site = 1)
    {
        $config = $GLOBALS['config'];

        if (!empty($config['Multisites-site' . $site])) {
            return $config['Multisites-site' . $site];
        }

        return '';
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