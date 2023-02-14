<?php

// MT39932 - remove HTML entities from frameworks title
$db = new db();
$db->select2('pl_poste_lignes');

if ($db->result) {
    foreach ($db->result as $elem) {
        $old = $elem['poste'];
        $new = html_entity_decode($elem['poste'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}pl_poste_lignes` SET `poste` = '$new' WHERE `id` = '{$elem['id']}';";
        }
    }
}

// MT39932 - remove HTML entities from users firstname and lastname
$db = new db();
$db->select2('personnel');

if ($db->result) {
    foreach ($db->result as $elem) {
        $oldLastName = $elem['nom'];
        $newLastName = html_entity_decode($elem['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $oldFirstName = $elem['prenom'];
        $newFirstName = html_entity_decode($elem['prenom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if (($newLastName != $oldLastName) or ($newFirstName != $oldFirstName)) {
            $newLastName = addslashes($newLastName);
            $newFirstName = addslashes($newFirstName);
            $sql[] = "UPDATE `{$dbprefix}personnel` SET `nom` = '$newLastName', `prenom` = '$newFirstName' WHERE `id` = '{$elem['id']}';";
        }
    }
}
