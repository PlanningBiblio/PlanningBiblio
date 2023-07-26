<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

/**
 * Start of Planno additions
 * TODO : All these checks must be done in listeners
 * TODO : includes and init files should be deleted
 */
include_once(__DIR__.'/../init/init.php');
include_once(__DIR__.'/../init/init_menu.php');
include_once(__DIR__.'/../init/init_templates.php');
include_once(__DIR__ . '/../init/common.php');

if (!empty($_SESSION['login_id'])) {
    require_once(__DIR__.'/include/cron.php');
}

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$base_url = plannoBaseUrl($request);

// Session has expired. Redirect to authentication page.
if (empty($_SESSION['login_id']) && !in_array($path, ['/login', '/logout', '/legal-notices'])) {

    $redirect = ltrim($path, '/');
    $redirect = !empty($redirect) ? "?redirURL=$redirect" : null;

    header("Location: $base_url/login{$redirect}");
    exit();
}

// Prevent user accessing to login page if he is already authenticated.
if (!empty($_SESSION['login_id']) && $path == '/login') {
    header("Location: {$config['URL']}");
    exit();
}
/**
 * End of Planno additions
 */

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
