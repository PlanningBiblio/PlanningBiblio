<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/ajax.control.php
Création : mai 2011
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de controler en arrière-plan si un agent est absent entre 2 dates.

Page appelée par la fonction javascript verif_absences utilisée par les page absences/ajouter.php et absences/modif.php
*/

require_once "../include/config.php";
require_once "class.absences.php";

$db=new db();
$db->query("SELECT * FROM `{$dbprefix}absences` WHERE `perso_id`='{$_GET['perso_id']}' AND `id`<>'{$_GET['id']}'  
  AND ((debut<='{$_GET['debut']}' AND fin>'{$_GET['debut']}') 
  OR (debut<'{$_GET['fin']}' AND fin>='{$_GET['fin']}') 
  OR (debut>='{$_GET['debut']}' AND fin <='{$_GET['fin']}'));");

$result=array("false");
if($db->result){
  $result=array("true");
  $result[]=dateFr($db->result[0]['debut'])." ".heure2(substr($db->result[0]['debut'],-8))." et le ".dateFr($db->result[0]['fin'])." ".heure2(substr($db->result[0]['fin'],-8));
}
echo json_encode($result);
?>