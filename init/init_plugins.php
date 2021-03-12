<?php

use Composer\Autoload\ClassMapGenerator;
use Composer\Autoload\ClassLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$plugins_dir = __DIR__ . '/../plugins';

if (file_exists($plugins_dir)) {
    $mapping = ClassMapGenerator::createMap($plugins_dir);
    $loader = new Composer\Autoload\ClassLoader($plugins_dir);
    $loader->register();

    foreach ($mapping as $className => $classPath){
        require_once($classPath);
        $plugin = new $className();

        foreach ($plugin->actions() as $name => $method) {
            $this->dispatcher->addListener($name, [$plugin, $method]);
        }
    }
}
