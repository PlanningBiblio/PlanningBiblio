<?php

 $dbname = 'personnel';

 $fields = array(
     'statut',
 );

 $old_characters = array('Ã©', 'Ã§', 'Ã¨', 'Ã¯', 'Ã«', 'Ã´', 'Ã',);
 $new_characters = array('é', 'ç', 'è', 'ï', 'ë', 'ô', 'à',);

 $fields_query = '`id`, `' . join('`, `', $fields) . '`';

 $req = "SELECT $fields_query FROM `{$dbprefix}{$dbname}`;";

 $db = new db();
 $db->query($req);

if ($db->result) {
    foreach ($db->result as $elem) {

        foreach ($fields as $field) {

            $value = $elem[$field];
            $value = str_replace($old_characters, $new_characters, $value);
            $value = html_entity_decode($value, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $value = addslashes($value);
            $sql[] = "UPDATE `{$dbprefix}{$dbname}` SET `$field` = '$value' WHERE `id`='{$elem['id']}';";
        }
    }
}