<?php
/**
Planning Biblio, Version 2.0.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planningHebdo/planning.php
Création : 22 mars 2014
Dernière modification : 7 novembre 2015
@author Jérôme Combes <jerome@planningbilbio.fr>

Description :
Fichier permettant de trouver le bon emploi du temps et le bon site de chaque agent
Inclus dans planning/poste/index.php pour afficher ces informations dans la liste des agents présents
Inclus dans planning/poste/menudiv.php pour exclure les agents non prévus sur le site séléctionné

Variables initialisées dans planning/poste/index.php et dans  planning/poste/menudiv.php :
$elem['id'] = id de l'agent
$date = date courante
*/

include_once "class.planningHebdo.php";
$p=new planningHebdo();
$p->debut=$date;
$p->fin=$date;
$p->valide=true;
$p->fetch();

$tempsPlanningHebdo=array();

if(!empty($p->elements)){
  foreach($p->elements as $elem){
    $tempsPlanningHebdo[$elem["perso_id"]]=$elem["temps"];
  }
}
?>
