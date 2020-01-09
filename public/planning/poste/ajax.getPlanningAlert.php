<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.getPlanningAlert.php
Création : 03 décembre 2019
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche une alerte pour les agents magasiniers du Fonds Général s'ils ne sont pas placés pendant 1 heure
Script appelé par $( "#pl-appelDispo-form" ).dialog({ Envoyer ]), planning/poste/js/planning.js
*/

ini_set("display_errors", 0);

// Includes
require_once "../../include/config.php";
require_once "../../include/function.php";
require_once "class.AgentsPlanning.php";
include_once(__DIR__.'/../../init_ajax.php');

use App\Model\Agent;

$CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_STRING);
$poste=filter_input(INPUT_GET, "poste", FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);
$debut=filter_input(INPUT_GET, "debut", FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET, "fin", FILTER_SANITIZE_STRING);
$agents=filter_input(INPUT_GET, "agents", FILTER_SANITIZE_STRING);
$sujet=filter_input(INPUT_GET, "sujet", FILTER_SANITIZE_STRING);
$message=filter_input(INPUT_GET, "message", FILTER_SANITIZE_STRING);
$sitename = $config["Multisites-site$site"];

/*
if ($sitename != 'Fond Général C') {
    exit;
}
*/

$agentsPlanning = new AgentsPlanning($date, $debut, $fin);
$agentsPlanning->removeExcluded();
$agentsPlanning->removeForTimes($debut, $fin);
$agentsPlanning->removeForAbsences(true);
$agentsPlanning->removeForHolidays(true);
$agentsPlanning->removeForOccupied();
$unplacedAgents = array();
$unplacedAgents['names'] = $agentsPlanning->getNames();
$unplacedAgents['amount'] = sizeof($agentsPlanning->getAvailables());
echo json_encode($unplacedAgents);


/*
$agents = $entityManager->getRepository(Agent::class)->findBy(['service' => 'Service au public']);
foreach ($agents as $agent) {
    $sitesarray = json_decode($agent->sites());

    # Agent belongs to site 'Fond Général C'
    if (!is_array($sitesarray) || !in_array($site, $sitesarray)) {
        continue;
    }

    if (!$agent->is_agent_status_in_category('Catégorie C')) {
        continue;
    }
    //error_log($agent->id());
}
*/
