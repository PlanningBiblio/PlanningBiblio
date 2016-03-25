<?php
/*
Licence GNU/GPL (version 2 et au dela)
 Voir les fichiers README.md et LICENSE
 @copyright 2011-2016 Jérôme Combes
 
Fichier : planning/poste/ajax.tmplock.php
Création : 16 mars 2016
Dernière modification : 16 mars 2016
@author Olivier Crouzet <olivier.crouzet@univ-lyon3.fr>
 
 Description :
Permet aux administrateurs de bloquer temporairement le depot d'absences en gardant la possibilité
de faire des modifications sur le planning (contrairement au verrouillage)
Page appelée en ajax lors du click sur le feu de signalisation de la page planning/poste/index.php
 (événements $("#icon-tmplock").click et $("#icon-tmpunlock").click, page planning/poste/js/planning.js)
*/
 
session_start();
require_once "../../include/config.php";
require_once "class.planning.php";
 
// Initialisation des variables
 $site=$_GET['site'];
 $blocage_dep_abs=$_GET['blocage_dep_abs'];
 $perso_id=$_SESSION['login_id'];
 
 if ( $blocage_dep_abs ) {
 	$db=new db();
	$insert=array("blocage_dep_abs"=>$blocage_dep_abs, "perso2"=>$perso_id, "site"=>$site);
 	$db->insert2("pl_poste_verrou",$insert);
 } else {
 	$db=new db();
	$db->delete("pl_poste_verrou","`blocage_dep_abs`= 1 and `perso2`= '$perso_id'");
 }
 echo json_encode(1);
 exit;
?>
