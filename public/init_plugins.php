<?php

use Composer\Autoload\ClassMapGenerator;
//use Symfony\Component\ClassLoader\MapClassLoader;
use Composer\Autoload\ClassLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

if (file_exists(__DIR__ . '/../plugins')) {
    $mapping = ClassMapGenerator::createMap(__DIR__ . '/../plugins');
    $loader = new ClassLoader($mapping);
    $loader->register();

    foreach ($mapping as $className => $classPath){
        require_once($classPath);
        $plugin = new $className();

        foreach ($plugin->actions() as $name => $method) {
            $this->dispatcher->addListener($name, [$plugin, $method]);
        }
    }
}
