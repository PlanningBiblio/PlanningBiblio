<?php

$db = new db();
$db->select2('absences', array('id', 'motif'), "`motif` LIKE '%&%'");

foreach ($db->result as $elem) {
  $id = $elem['id'];
  $old = $elem['motif'];

  $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

  if ($new != $old) {
    $new = addslashes($new);
    $sql[] = "UPDATE `{$dbprefix}absences` SET `motif` = '$new' WHERE `id` = '$id';";
  }
}

$db = new db();
$db->select2('select_abs', array('id', 'valeur'), "`valeur` LIKE '%&%'");

foreach ($db->result as $elem) {
  $id = $elem['id'];
  $old = $elem['valeur'];

  $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

  if ($new != $old) {
    $new = addslashes($new);
    $sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = '$new' WHERE `id` = '$id';";
  }
}