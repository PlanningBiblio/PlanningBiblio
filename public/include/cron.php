<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/cron.php
Création : 23 juillet 2013
Dernière modification : 13 février 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Script exécutant les taches planifiées enregistrées dans la table cron
A améliorer :  pour le moment, chaque ligne est exécutée à la première connexion de la journée,
  sans vérifier les paramétres heures, minutes, dom, mon et dow

Page appelée par le fichier index.php
*/

// Dates
$dateCron=date('Y-m-d');
$dom=date("j");
$mon=date("n");

// Daily Cron
$dbCron=new db();
$dbCron->select("cron", "*", "dom='*' and mon='*' and dow='*' and last<'$dateCron'");
if ($dbCron->result) {
    foreach ($dbCron->result as $elemCron) {
        include $elemCron['command'];
        $dbCron2=new db();
        $dbCron2->CSRFToken = $CSRFSession;
        $dbCron2->update('cron', array('last'=>'SYSDATE'), array('id'=>$elemCron['id']));
    }
}

// Absences : Met à jour la table absences avec les événements récurrents sans date de fin (J + 2ans)
// 1 fois par jour
require_once __DIR__."/../absences/class.absences.php";
$a = new absences();
$a->CSRFToken = $CSRFSession;
$a->ics_update_table();


// Yearly Cron
// Recherche les crons ayant dom != * et mon != *
$dbCron=new db();
$dbCron->select2("cron", null, array('dom' => '<>*',  'mon' => '<>*'));

// Le tableau $commands permet de ne pas exécuter 2 fois la même commande, au cas où il y aurait des doublons dans la base de données
$commands = array();

if ($dbCron->result) {
    foreach ($dbCron->result as $elemCron) {
  
    // Pour chaque résultat, si la commande n'a pas encore été exécutée
        if (!in_array($elemCron['command'], $commands)) {
            $commands[] = $elemCron['command'];
      
            // On constitue la date à laquelle la commande doit être exécutée
            $command_date = strtotime("{$elemCron['mon']}/{$elemCron['dom']}");
            if ($command_date > time()) {
                $command_date = strtotime('-1 year', $command_date);
            }
      
            $command_date = date('Y-m-d 00:00:00', $command_date);

            // Si la commande n'a pas été exécutée depuis la date prévue
            if ($elemCron['last'] < $command_date) {
      
        // on exécute la commande
                include $elemCron['command'];
        
                // On met à jour la date dans la base de données
                $dbCron2=new db();
                $dbCron2->CSRFToken = $CSRFSession;
                $dbCron2->update('cron', array('last'=>'SYSDATE'), array('command'=>$elemCron['command']));
            }
        }
    }
}
