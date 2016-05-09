<?php
/**
Planning Biblio, Version 2.2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/poste/ajax.tmplock.php
Création : 23 février 2015
Dernière modification : 3 avril 2015
@author Olivier Crouzet <olivier.crouzet@univ-lyon3.fr>

Description :
Permet aux administrateurs de bloquer temporairement (et débloquer) le depot d'absence
tout en gardant la possibilité de modifier le planning
Page appelée en ajax lors du click sur les maillons de chaine de la page planning/poste/index.php
(événements $("#icon-tmplock").click et $("#icon-tmpunlock").click, page planning/poste/js/planning.js)
*/

session_start();
require_once "../../include/config.php";
require_once "class.planning.php";

//~ // Initialisation des variables
$date=$_GET['date'];
$site=$_GET['site'];
$blocage=$_GET['blocage_dep_abs'];
$perso_id=$_SESSION['login_id'];

$db=new db();
$db->select2("pl_poste_verrou","*",array("date"=>$date, "site"=>$site));
// si le planning du jour est déja verrouillé
if ( $db->result ) {
    $id_verrou = $db->result[0]['id'];
    $set=array("blocage_dep_abs"=>$blocage);
    $where=array("id"=>$id_verrou);
    $db=new db();
    $db->update2("pl_poste_verrou",$set,$where);
} else {
    if ( $blocage ) {
      $db=new db();
      $insert=array("date"=>$date, "blocage_dep_abs"=>$blocage, "perso2"=>$perso_id, "site"=>$site);
      $db->insert2("pl_poste_verrou",$insert);
    } else {
      $db=new db();
      $db->delete("pl_poste_verrou","`date`= '$date' and `blocage_dep_abs`= 1 and `perso2`= '$perso_id'");
    }
}
echo json_encode(1);
exit;
?>
