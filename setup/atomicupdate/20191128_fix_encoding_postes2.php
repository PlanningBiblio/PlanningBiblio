<?php

$dbname = 'postes';

$fields = array(
    'nom',
);

$old_characters = array('Ã©', 'Ã§', 'Ã¨', 'Ã¯', 'Ã«', 'Ã´', 'Ã',);
$new_characters = array('é', 'ç', 'è', 'ï', 'ë', 'ô', 'à',);

$fields_query = '`id`, `' . join('`, `', $fields) . '`';

$req = "SELECT $fields_query FROM `{$dbprefix}{$dbname}`;";

$db = new db();
$db->query($req);

foreach ($db->result as $elem) {

    foreach ($fields as $field) {

	$value = $elem[$field];

	foreach ($old_characters as $char) {

	    if (strstr($value, $char)) {
	        $value = str_replace($old_characters, $new_characters, $value);
    	        $sql[] = "UPDATE `{$dbprefix}{$dbname}` SET `$field` = '$value' WHERE `id`='{$elem['id']}';";
	    }
	}
    }
}
