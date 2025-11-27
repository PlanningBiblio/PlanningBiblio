<?php
require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Dotenv\Dotenv;

exec(__DIR__ . '/../vendor/bin/bdi detect drivers');

$_SERVER['APP_ENV'] = 'test';
$_SERVER['KERNEL_CLASS'] = 'App\Kernel';

if (!file_exists(__DIR__ . "/../.env.test.local")) {
    die("Unable to find the .env.test.local file\n");
}

(new Dotenv())->load(__DIR__ . "/../.env.test.local");

require_once 'init_db.php';

if ($dbconn) {
    foreach ($sql as $elem) {
        mysqli_multi_query($dblink, $elem);
    }
    mysqli_close($dblink);
}

// Run migrations
exec(__DIR__ . '/../bin/console doctrine:migrations:migrate --env=test -q');

include_once(__DIR__ . '/../init/init.php');
include_once(__DIR__.'/../init/init_templates.php');

$entitiesPath = array('src/Entity');
$emConfig = Setup::createAttributeMetadataConfiguration($entitiesPath, true);

$dbParams = array(
    'driver'   => 'pdo_mysql',
    'host'     => $config['dbhost'],
    'user'     => $config['dbuser'],
    'password' => $config['dbpass'],
    'dbname'   => $config['dbname'],
);

global $entityManager;
$entityManager = EntityManager::create($dbParams, $emConfig);
