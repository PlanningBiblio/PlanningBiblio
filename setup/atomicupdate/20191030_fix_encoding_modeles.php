<?php

 $dbname = 'pl_poste_modeles';

 $fields = array(
     'nom',
 );

 $fields_query = '`id`, `' . join('`, `', $fields) . '`';

 $req = "SELECT $fields_query FROM `{$dbprefix}{$dbname}`;";

 $db = new db();
 $db->query($req);

 foreach ($db->result as $elem) {
     foreach ($fields as $field) {
     	$test = mb_detect_encoding($elem[$field], 'UTF-8', true);

     	if ($test === false) {
     	    $value = utf8_encode($elem[$field]);
     	    $sql[] = "UPDATE `{$dbprefix}{$dbname}` SET `$field` = '$value' WHERE `id`='{$elem['id']}';";
    	 }

     }

 }
