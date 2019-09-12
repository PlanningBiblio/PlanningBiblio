<?php
/**
Planning Biblio
@file : public/include/config.php
@author Jérôme Combes <jerome@planningbiblio.fr>

@note :
Get DB settings and secret key from .env.local and provide legacy code with them.
*/

require_once(__DIR__.'/../../vendor/autoload.php');

use Symfony\Component\Dotenv\Dotenv;

// Security : Allow direct access to ajax files : add $version = 'ajax';
if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
  $version = 'ajax';
}

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../../.env.local');

global $config;
$config=Array();

// Get DB settings from .env.local
// DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name

$database_url = $_ENV['DATABASE_URL'];

$pattern = '/.[^\/]*\/\/(.[^:]*):(.[^@]*)@(.[^:]*):(\d*)\/(.*)/';

$config['dbuser'] = preg_replace($pattern, '\1', $database_url);
$config['dbpass'] = preg_replace($pattern, '\2', $database_url);
$config['dbhost'] = preg_replace($pattern, '\3', $database_url);
$config['dbport'] = preg_replace($pattern, '\4', $database_url);
$config['dbname'] = preg_replace($pattern, '\5', $database_url);

$config['dbprefix'] = $_ENV['DATABASE_PREFIX'];

$config['secret'] = $_ENV['APP_SECRET'];

$dbprefix = $config['dbprefix'];

include 'db.php';

// Get config values from DB
$db = new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `id`;");
foreach ($db->result as $elem) {
  $config[$elem['nom']] = $elem['valeur'];
}

/** Get custom options
 * custom_options.php may contain extra config values
 * Example : $config['demo'] = 1; $config['demo-password'] = 'My_demo_password';
 */
$custom_options_file = __DIR__ . '/../../custom_options.php';

if (!empty($_ENV['CUSTOM_OPTIONS'])) {
    $custom_options_file = $_ENV['CUSTOM_OPTIONS'];
}
if (file_exists($custom_options_file)) {
    include_once($custom_options_file);
}

// $version not set means direct access to an unauthorized file ==> load the access denied page
/*
if (!isset($version)) {
  include_once "accessDenied.php";
}*/
