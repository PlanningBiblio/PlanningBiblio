<?php
/**
Planning Biblio, Version 2.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : postes/ajax.groupes.php
Création : 5 février 2017
Dernière modification : 5 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre la liste des groupes de postes dans la base de données
Appelé lors du clic sur le bouton "Enregistrer" de la dialog box "Liste des groupes" à partir de la fiche poste
*/

ini_set('display_errors',0);

session_start();

include "../include/config.php";
$tab=json_decode($_POST['tab']);

$db=new db();
$db->delete("select_groupes");
foreach($tab as $elem){
  $db=new db();
  $db->insert2("select_groupes",array("valeur"=>$elem[0],"rang"=>$elem[2]));
}
?>