<?php
/**
Planning Biblio, Plugin Conges Version 2.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/agenda.php
Création : 14 mars 2014
Dernière modification : 27 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier intégré dans l'agenda (agenda/index.php)
Ajoute les informations sur les congés dans l'agenda
Modifie les variables $absent et $absences_affichage initiées dans le fichier agenda/index.php
*/

/*
$absent = true si une absence sur toute la journée est enregistrée, permet de ne pas afficher les horaires habituels dans ce cas
$absences_affichage = message d'absence (absent(e) toute la journée, absent de telle à telle heure)
$current = date de la cellule courante
$perso_id = id de l'agent
$current_postes = liste des postes occupés avec heures de début, de fin, et indicateur "absent"
*/
// TODO : fusion des affichage si les congés se suivent sur une journée (ex : 2016-02-25 09:00:00-10:00:00 suivi de 2016-02-25 10:00:00-17:00:00)

include_once "class.conges.php";
$c=new conges();
$c->perso_id=$perso_id;
$c->debut=$current." 00:00:00";
$c->fin=$current." 23:59:59";
$c->valide=true;
$c->fetch();
$conges_affichage=array();

if (!empty($c->elements)) {
    for ($i=0;$i<count($c->elements);$i++) {
        $conge=$c->elements[$i];
        // Si en congé toute la journée, n'affiche pas les horaires de présence habituels et les absences enregistrées
        // (remplace le message d'absence)
        if ($conge['debut']<=$current." 00:00:00" and $conge['fin']>=$current." 23:59:59") {
            $absent=true;
            $conges_affichage[]="Toute la journ&eacute;e : Cong&eacute;";
        } elseif (substr($conge['debut'], 0, 10)==$current and substr($conge['fin'], 0, 10)==$current) {
            $deb=heure2(substr($conge['debut'], -8));
            $fi=heure2(substr($conge['fin'], -8));
            $conges_affichage[]="De $deb &agrave; $fi : Cong&eacute;";
        } elseif (substr($conge['debut'], 0, 10)==$current and $conge['fin']>=$current." 23:59:59") {
            $deb=heure2(substr($conge['debut'], -8));
            $conges_affichage[]="&Agrave; partir de $deb : Cong&eacute;";
        } elseif ($conge['debut']<=$current." 00:00:00" and substr($conge['fin'], 0, 10)==$current) {
            $fi=heure2(substr($conge['fin'], -8));
            $conges_affichage[]="Jusqu'&agrave; $fi : Cong&eacute;";
        } else {
            $conges_affichage[]="{$conge['debut']} &rarr; {$conge['fin']} : Cong&eacute;";
        }

        // Modifie l'index "absent" du tableau $current_postes pour barrer les postes concernés par le congé
        for ($j=0;$j<count($current_postes);$j++) {
            if ($current." ".$current_postes[$j]['debut']<$conge['fin'] and $current." ".$current_postes[$j]['fin']>$conge['debut']) {
                $current_postes[$j]['absent']=1;
            }
        }
    }
}

// Si congé sur une partie de la journée seulement, complète le message d'absence
if (!empty($conges_affichage)) {
    $absences_affichage=array_merge($absences_affichage, $conges_affichage);
}
