<?php

namespace App\Asset\VersionStrategy;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class CurrentVersionStrategy implements VersionStrategyInterface
{
    private $version;

    public function __construct()
    {
        $this->version = $GLOBALS['version'];
    }

    public function getVersion($path)
    {
        return $this->version;
    }

    public function applyVersion($path)
    {
        if (substr($path, 0, 3) == 'js/'
            or substr($path, 0, 7) == 'themes/'
            or substr($path, -3) == '.js') {
            return sprintf('%s?version=%s', $path, $this->getVersion($path));
        }

        return $path;
    }
}
