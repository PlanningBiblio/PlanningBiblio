<?php
/*
Planning Biblio, Plugin Congés Version 1.5.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.verifConges.php
Création : 12 février 2014
Dernière modification : 3 juin 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Vérifie si la période demandée a déjà fait l'objet d'une demande de congés.
Appelé en arrière plan par la fonction JS verifConges()
*/

include(__DIR__.'/../init_ajax.php');
include "class.conges.php";

$debut=$_GET['debut'];
$fin=$_GET['fin']?$_GET['fin']:$debut;
$fin=$fin;
$hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
$hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
$perso_id=$_GET['perso_id'];
$id=$_GET['id'];

if ($result = conges::exists($perso_id, "$debut $hre_debut", "$fin $hre_fin", $id)) {
    echo 'du ' . dateFr($result['from'], true) . ' au ' . dateFr($result['to'], true);
} else {
    echo "Pas de congé";
}
