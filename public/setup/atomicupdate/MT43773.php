<?php

$db = new db();
$db->select2('conges', ['id', 'commentaires', 'refus']);

if ($db->result) {
    foreach ($db->result as $elem) {

        $comment = addslashes(html_entity_decode($elem['commentaires']));
        $refusal = addslashes(html_entity_decode($elem['refus']));

        if ($comment != $elem['commentaires'] or $refusal != $elem['refus']) {
            $sql[] = "UPDATE `{$dbprefix}conges` SET `commentaires` = '$comment', `refus` = '$refusal' WHERE `id` = {$elem['id']};";
        }
    }
}
