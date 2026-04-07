<?php

namespace App\Twig;

use App\Entity\Site;
use App\Planno\Helper\HolidayHelper;
use App\Planno\Helper\HourHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

include_once(__DIR__ . '/../../legacy/Common/function.php');
include_once(__DIR__ . '/../../legacy/Common/feries.php');
include_once(__DIR__ . '/../../legacy/Class/class.planningFunctions.php');

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
            new TwigFilter('dateOrNull', [$this, 'dateOrNull']),
            new TwigFilter('dateOrTime', [$this, 'dateOrTime']),
            new TwigFilter('digit', [$this, 'digit']),
            new TwigFilter('hours', [$this, 'hours']),
            new TwigFilter('hour_from_his', [$this, 'hourFromHis']),
            new TwigFilter('hoursToDays', [$this, 'hoursToDays']),
            new TwigFilter('raw_black_listed', [$this, 'htmlFilter'], ['is_safe' => ['html']]),
            new TwigFilter('sites', [$this, 'sites']),
            new TwigFilter('time', [$this, 'time']),
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
            new TwigFunction('itemIsActive', [$this, 'itemIsActive']),
            new TwigFunction('menuIsActive', [$this, 'menuIsActive']),
            new TwigFunction('colspan', [$this, 'colspan']),
        ];
    }

    public function dateFull($date, bool $day = true, bool $year = true)
    {
        return dateAlpha($date, $day, $year);
    }

    public function dateFr($date): ?string
    {
        return dateFr($date, true);
    }

    public function dateOrNull($date, $format): ?string
    {
        if (empty($date)) {
            return null;
        }

        return $date->format($format);
    }

    public function dateOrTime($date, $format): ?string
    {
        if (empty($date)) {
            return null;
        }

        $now = new \DateTime();
        $today = \DateTime::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d 00:00:00'));

        if ($date >= $today) {
            $format = substr($format, strpos($format, ' '));
        }

        return $date->format($format);
    }

    public function digit($number, $digits): string
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

    public function hourFromHis($hours): string
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

    public function siteName($site = 1): string
    {
        $s = $GLOBALS['entityManager']->getRepository(Site::class)->find($site);
        return $s ? $s->getName() : '';
    }

    public function hoursToDays($hours, $perso_id): string
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

    public function itemIsActive($itemUrl, $requestedUrl, $session): bool
    {
        $config = $GLOBALS['config'];
        $url = $config['URL'] . '/' . $itemUrl;
        $site = $session->get('site');

        // Handle Planning's menu

        // If URL ends with a date or /week check site 
        if (preg_match('/(.+)(\/[0-9]{4}((-[0-9]{2}){2})|\/week)/', $requestedUrl)){
            return $url === ($config['URL'] . '/' . $site);
        }

        // If URL ends with site number
            if (preg_match('/(.+)(\/[0-9]{1})/', $requestedUrl, $match) and $match[1]===$config['URL']){
            return $url === $requestedUrl;
        }

        // if URL empty
        if ($requestedUrl===($config['URL'] . '/')){
            return $url === ($config['URL'] . '/' . $site);
        }

        // Specific case for /absence/add
        if (preg_match('/absence\/add/', $requestedUrl)){
            return $url === $requestedUrl;
        }

        // Find the level-up URL for all routes ending in 'add' or in any number for edit
        if (preg_match('/(.+?)(-.+)?\/add/', $requestedUrl, $match) or preg_match('/(.+?)(-.+)?(\/[0-9]+)/', $requestedUrl, $match)){
            return $url === $match[1];
        }

        // Find the origin URL without the route parameters     
        if (preg_match('/^([^?]*)/', $requestedUrl, $match)){
            return $url === $match[0];
        }

    }

    public function menuIsActive($menu, $requested_url): bool
    {
        $config = $GLOBALS['config'];

        // Handle Planning's menu
        if (empty($menu)) {

            (preg_match('/^([^?]*)/', $requested_url, $match));
            $uri = substr($match[0], strlen($config['URL']));

            return (bool) preg_match('/(\/[0-9]{4}((-[0-9]{2}){2})|\/week|\/detached|\A\/$|\A\/[0-9]{1,2}$)/', $uri);
        }

        if(strpos($requested_url, "{$config['URL']}/$menu") !== false){
            return true;
        }

        // Handle specific admin menu
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

        if ($menu == 'holiday') {
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

    public function sites($sites): string
    {
        if (!is_array($sites)) {
            return '';
        }

        $displayedSites = [];
        foreach ($sites as $site) {
            $s = $GLOBALS['entityManager']->getRepository(Site::class)->find($site);
            if ($s !== null) {
                $displayedSites[] = $s->getName();
            }
        }

        return implode(', ', $displayedSites);
    }

    public function time($time): string
    {
        if (is_numeric($time)) {
            $hourHelper = new HourHelper();
            $time = $hourHelper->decimalToHoursMinutes($time);
            return $time['as_string'];
        }

        return $time;
    }
}
