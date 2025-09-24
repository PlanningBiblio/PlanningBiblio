<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use App\PlanningBiblio\Helper\HolidayHelper;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/include/feries.php');
include_once(__DIR__ . '/../../public/planning/poste/fonctions.php');

class AppExtension extends AbstractExtension
{

    private $blacklistedTags = ['script'];

    /**
     * @return array
    */
    public function getFilters()
    {
        return [
            new TwigFilter('datefull', [$this, 'dateFull'], ['is_safe' => ['html']]),
            new TwigFilter('datefr', [$this, 'dateFr']),
            new TwigFilter('digit', [$this, 'digit']),
            new TwigFilter('hours', [$this, 'hours']),
            new TwigFilter('hour_from_his', [$this, 'hourFromHis']),
            new TwigFilter('hoursToDays', [$this, 'hoursToDays']),
            new TwigFilter('raw_black_listed', [$this, 'htmlFilter'], ['is_safe' => ['html']]),
        ];
    }

    /**
    * @return array
    */
    public function getFunctions()
    {
        return [
            new TwigFunction('config', [$this, 'getConfig']),
            new TwigFunction('siteName', [$this, 'siteName']),
            new TwigFunction('userCan', [$this, 'userCan']),
            new TwigFunction('menuIsActive', [$this, 'menuIsActive']),
            new TwigFunction('colspan', [$this, 'colspan']),
        ];
    }

    public function dateFull($date)
    {
        return dateAlpha($date);
    }

    public function dateFr($date)
    {
        return dateFr($date, true);
    }

    public function digit($number, $digits)
    {
        return sprintf('%0' . $digits . 'd', $number);
    }

    public function hours($hours)
    {
        if ($hours) {
            return heure4($hours);
        }

        return '';
    }

    public function hourFromHis($hours)
    {
        if ($hours) {
            return heure3($hours);
        }

        return '';
    }

    public function userCan($right, $site = 0): bool
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

    public function menuIsActive($menu, $requested_url): bool
    {
        $config = $GLOBALS['config'];

        // Handle Planning's menu
        if (empty($menu)) {
            $uri = substr($requested_url, strlen($config['URL']));

            if ($uri == '/') {
                return true;
            }

            if (preg_match('/^\/(\/d{4}-\d{2}-\d{2})/', $uri)) {
                return true;
            }
            return (bool) preg_match('/^\/(\d+)(\/d{4}-\d{2}-\d{2})?/', $uri);
        }

        if(strpos($requested_url, "{$config['URL']}/$menu") !== false){
            return true;
        }

        // Handle specfic admin menu
        if ($menu == 'admin') {
            $admin_pages = array(
                'skill', 'agent', 'position',
                'model', 'framework', 'closingday',
                'workinghour', 'config', 'notification');

            foreach ($admin_pages as $page) {
                if(strpos($requested_url, "{$config['URL']}/$page") !== false){
                    return true;
                }
            }
        }

        if ($menu == 'holiday/index') {
            if (strpos($requested_url, 'holiday') !== false) {
                return true;
            }
            if (strpos($requested_url, 'comptime') !== false) {
                return true;
            }
            if (strpos($requested_url, 'overtime') !== false) {
                return true;
            }
        }

        if ($menu == 'index') {
            if (strpos($requested_url, 'week') !== false) {
                return true;
            }
        }

        return false;
    }

    public function htmlFilter($html)
    {
	if (!$html)
	  return $html;

        foreach ($this->blacklistedTags as $tag) {
            $html = preg_replace("/<$tag.*?>(.*)?<\/$tag>/im","$1",$html);
        }

        return $html;
    }

    public function colspan($start, $end) {
        return nb30($start, $end);
    }

}
