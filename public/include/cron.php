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

use App\Cron\Crontab;

if (php_sapi_name() != 'cli') {

    $crontab = new Crontab();
    $crons = $crontab->crons();

    foreach ($crons as $cron) {
        include(Crontab::$crons_dir . $cron->command());
        Crontab::update_cron($cron);
    }

    // Absences : Met à jour la table absences avec les événements récurrents sans date de fin (J + 2ans)
    // 1 fois par jour
    require_once __DIR__ . '/../absences/class.absences.php';

    $a = new absences();
    $a->CSRFToken = $CSRFSession;
    $a->ics_update_table();
}
