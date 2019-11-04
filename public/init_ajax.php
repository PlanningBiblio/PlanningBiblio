<?php
/**
 * Planning Biblio
 * Licence GNU/GPL (version 2 et au dela)
 * see README.md et LICENSE
 * @copyright 2011-2019 Jérôme Combes

 * File : init_ajax.php
 * Created: 2019-02-06
 * Last change: 2019-02-06
 * @author Alex Arnaud <alex.arnaud@biblibre.com>

 * Description :
 * Init session, and entity manager for ajax scripts
 */

session_start();

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/include/config.php');
require_once(__DIR__.'/init_entitymanager.php');
