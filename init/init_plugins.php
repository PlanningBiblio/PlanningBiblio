<?php

use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

if (file_exists(__DIR__ . '/../plugins')) {
    $path = array(__DIR__.'/../plugins');
    $loader = new \Composer\Autoload\ClassLoader();
    $loader->addClassMap($path);
    $loader->register();

    foreach ($mapping as $className => $classPath){
        require_once($classPath);
        $plugin = new $className();

        foreach ($plugin->actions() as $name => $method) {
            $this->dispatcher->addListener($name, [$plugin, $method]);
        }
    }
}
