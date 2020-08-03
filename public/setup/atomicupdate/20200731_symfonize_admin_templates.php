<?php

$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles_tab` ADD `model_id` INT(11) NOT NULL DEFAULT '0' AFTER `nom`;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles` ADD `model_id` INT(11) NOT NULL DEFAULT '0' AFTER `nom`;";

$db = new db();
$db->select2('pl_poste_modeles_tab', array('id', 'nom', 'site'), null, "ORDER BY `site`, `nom`, `id`");

$last = null;
$model = 0;

if ($db->result) {
    foreach ($db->result as $elem) {
        if ($elem['nom'] . '_' . $elem['site'] != $last) {
            $model++;
            $last = $elem['nom'] . '_' . $elem['site'];
        }
        $sql[] = "UPDATE `{$dbprefix}pl_poste_modeles_tab` SET `model_id` = '$model' WHERE `id` = '{$elem['id']}';";
        $sql[] = "UPDATE `{$dbprefix}pl_poste_modeles` SET `model_id` = '$model' WHERE `nom` = '{$elem['nom']}';";
    }
}

$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles` DROP COLUMN `nom`;";

$db = new db();
$db->select2('pl_poste_modeles_tab', array('id', 'nom'), "nom LIKE '%&%'");
if ($db->result) {
    foreach ($db->result as $elem) {
        $nom = html_entity_decode($elem['nom']);
        $sql[] = "UPDATE `{$dbprefix}pl_poste_modeles_tab` SET `nom` = '$nom' WHERE `id` = '{$elem['id']}';";
    }
}