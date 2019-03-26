<?php
/**
  * Planning Biblio
  * Licence GNU/GPL (version 2 et au dela)
  * @copyright 2019 Biblire
  *
  * file: setup/updatedatabase.php
  * @author Alex arnaud <alex.arnaud@biblibre.com >
  *
  * Runs all atmoic update files in setup/atomicupdate/
  */

require_once(__DIR__ . '/../init.php');
require_once(__DIR__ . '/../include/db.php');

$atomic_dir = __DIR__ . '/../setup/atomicupdate/*.php';

$sql = array();
foreach (glob($atomic_dir) as $file) {
    print basename($file) . ': ';
    try {
        require_once($file);
    } catch (Exception $e) {
        print $e->getMessage() . "\n";
        continue;
    }

    foreach ($sql as $queries) {
        $db = new db();
        $db->query($queries);
        if ($db->error) {
            print $db->error . "\n";
            continue;
        }
        print "Ok\n";
    }
    $sql = array();
}
?>
