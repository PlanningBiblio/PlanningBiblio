<?php
require_once 'vendor/autoload.php';
$tplDir = dirname(__FILE__).'/templates';
$tmpDir = '/tmp/cache/';
$loader = new Twig_Loader_Filesystem($tplDir);

// force auto-reload to always have the latest version of the template
$twig = new Twig_Environment($loader, array(
    'cache' => $tmpDir,
    'auto_reload' => true
));
$twig->addExtension(new Twig_Extensions_Extension_I18n());
$twig->addExtension(new Symfony\Bridge\Twig\Extension\AssetExtension(
    new Symfony\Component\Asset\Packages()
));
$twig->addExtension(new Symfony\Bridge\Twig\Extension\RoutingExtension(
    new Twig\Gettext\Routing\Generator\UrlGenerator()
));
$twig->addExtension(new Symfony\Bridge\Twig\Extension\FormExtension(
    new Symfony\Bridge\Twig\Form\TwigRenderer(
        new Symfony\Bridge\Twig\Form\TwigRendererEngine()
    )
));

// configure Twig the way you want


// iterate over all your templates
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
    // force compilation
    if ($file->isFile()) {
        $twig->loadTemplate(str_replace($tplDir.'/', '', $file));
    }
}
?>
