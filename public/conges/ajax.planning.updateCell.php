<?php
/**
Planning Biblio, Plugin Conges Version 2.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.planning.updateCell.php
Création : 25 novembre 2014
Dernière modification : 16 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier intégré au fichier planning/poste/ajax.updateCell.php permettant la mise à jour de la cellule modifiée
Vérifie si les agents de la cellule modifiée sont en congés et les marques si c'est le cas
*/

/*
Variables en entrée
  $site,	$ajouter,	$perso_id,	$perso_id_origine,	$date
  $debut,	$fin,		$absent,	$poste,			$barrer
  $tab :
    [0] => Array (
      [nom] => Nom
      [prenom] => Prénom
      [statut] => Statut
      [service] => Service
      [perso_id] => 86
      [absent] => 0
      [supprime] => 0
      )
    [1] => Array (
      ...

Variable modifiée
  $tab :
    [0] => Array (
      [nom] => Nom
      [prenom] => Prénom
      [statut] => Statut
      [service] => Service
      [perso_id] => 86
      [absent] => 0
      [supprime] => 0
      [conges] => 0/1/2 ( 0 = pas d'absence ; 1 = absence validée ; 2 = absence non validée )
      )
    [1] => Array (
      ...
*/

require_once "class.conges.php";

$perso_ids=array();
foreach ($tab as $elem) {
    $perso_ids[]=$elem['perso_id'];
}
$perso_ids=implode(",", $perso_ids);

$c=new conges();
$c->debut="$date $debut";
$c->fin="$date $fin";
$c->information = false;
$c->supprime = false;
$c->valide=false;
$c->bornesExclues=true;
$c->fetch();

if (!empty($c->elements)) {
    for ($i=0;$i<count($tab);$i++) {
        $tab[$i]['conges']=0;
        foreach ($c->elements as $elem) {
            if ($tab[$i]['perso_id']==$elem['perso_id']) {
                if ($elem['valide']>0) {
                    $tab[$i]['conges']=1;
                    continue;  // Garder le continue à cet endroit pour que les absences validées prennent le dessus sur les non-validées
                } else {
                    $tab[$i]['conges']=2;
                }
            }
        }
    }
}
