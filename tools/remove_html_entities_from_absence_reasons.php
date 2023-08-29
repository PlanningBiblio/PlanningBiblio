<?php
// Remove HTML entities from absences and select_abs tables

require_once(__DIR__ . '/../init/init.php');
require_once(__DIR__ . '/../public/include/db.php');


$tables = array(
    array(
        'name' => 'absences',
        'field' => 'motif',
    ),
    array(
        'name' => 'select_abs',
        'field' => 'valeur',
    ),
);


$sql = array();

foreach ($tables as $table) {

    $name = $table['name'];
    $field = $table['field'];
    
    $db = new db();
    $db->sanitize_string = true;
    $db->select2($name, array('id', $field), "`$field` LIKE '%&%'");

    if (is_array($db->result)) {
        foreach ($db->result as $elem) {
            $id = $elem['id'];
            $old = $elem[$field];

            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}$name` SET `$field` = '$new' WHERE `id` = '$id';";
            }
        }
    }
}

foreach ($sql as $queries) {
    print $queries . " : ";

    $db = new db();
    $db->sanitize_string = true;
    $db->query($queries);
    if ($db->error) {
        print $db->error . "\n";
        continue;
    }
    print "Ok\n";
}
