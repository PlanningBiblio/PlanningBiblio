<?php
/**
Planning Biblio, Version 2.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : postes/ajax.menus.php
Création : 5 février 2017
Dernière modification : 5 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre la liste des groupes de postes et des étages dans la base de données
Appelé lors du clic sur le bouton "Enregistrer" de la dialog box "Liste des groupes" ou "Lsite des étages" à partir de la fiche poste
*/

ini_set('display_errors',0);

session_start();

include "../include/config.php";
$menu = FILTER_INPUT(INPUT_POST, 'menu', FILTER_SANITIZE_STRING);
$tab = $_POST['tab'];

$db=new db();
$db->delete("select_$menu");
foreach($tab as $elem){
  $db=new db();
  $db->insert2("select_$menu",array("valeur"=>$elem[0],"rang"=>$elem[2]));
}
echo json_encode('ok');
?>