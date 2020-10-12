<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use App\PlanningBiblio\Helper\HolidayHelper;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/include/feries.php');

class AppExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('datefull', [$this, 'dateFull']),
            new TwigFilter('hours', [$this, 'hours']),
            new TwigFilter('hoursToDays', [$this, 'hoursToDays']),
            new TwigFilter('hour', [$this, 'formatHour']),
            new TwigFilter('datehour', [$this, 'formatDateHour']),
            new TwigFilter('publicholiday', [$this, 'formatPublicHoliday']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('config', [$this, 'getConfig']),
            new TwigFunction('siteName', [$this, 'siteName']),
            new TwigFunction('userCan', [$this, 'userCan']),
            new TwigFunction('menuIsActive', [$this, 'menuIsActive']),
        ];
    }

    public function dateFull($date)
    {
        return dateAlpha($date);
    }

    public function hours($hours)
    {
        if ($hours) {
            return heure4($hours);
        }

        return '';
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

    public function hoursToDays($hours, $perso_id)
    {
        $holiday_helper = new HolidayHelper();
        if ($hours && $perso_id) {
            return $holiday_helper->hoursToDays($hours, $perso_id) .'j';
        }

        return '';
    }

    /**
     * formatHour($hour)
     * 08:00:00 => 8h
     * 11:30:00 => 11h30
     **/
    public function formatHour($hour)
    {
        return heure3($hour);
    }

    /**
     * formatDateHour($date)
     * 2019-11-20 08:30:00 => 20/11/2019 8h30
     * 2019-11-20 00:00:00 => 20/11/2019
     * 2019-11-20 23:59:59 => 20/11/2019
     **/
    public function formatDateHour($date)
    {
        list($date, $hour) = explode(' ', $date);
        $dt = new \DateTime($date);
        $date = $dt->format('d/m/Y');

        if ($hour == '00:00:00' || $hour == '23:59:59') {
            return $date;
        }

        $hour = heure3($hour);

        return "$date $hour";
    }

    /**
     * formatPublicHoliday($date)
     * 2019-11-11 => Armistice
     **/
    public function formatPublicHoliday($date)
    {
        return jour_ferie($date);
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

    public function menuIsActive($menu, $requested_url)
    {
        $base_url = $GLOBALS['base_url'];

        if(strpos($requested_url, "$base_url/$menu") !== false){
            return true;
        }

        // Handle specfic admin menu
        if ($menu == 'admin') {
            $admin_pages = array(
                'skill', 'agent', 'position',
                'model', 'framework', 'closingday',
                'workinghour', 'config', 'notification');

            foreach ($admin_pages as $page) {
                if(strpos($requested_url, "$base_url/$page") !== false){
                    return true;
                }
            }
        }

        if ($menu == 'holiday/index') {
            if (strpos($requested_url, 'holiday') !== false) {
                return true;
            }
            if (strpos($requested_url, 'comp-time') !== false) {
                return true;
            }
        }

        return false;
    }
}
