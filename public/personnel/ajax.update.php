<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

Fichier : public/personnel/ajax.update.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Met à jour les fiches des agents sélectionnés à partir de la liste des agents (fichier personnel/index.php).

Ce script est appelé par la fonction JS personnel/js/index.js : agent_list
*/

use App\Model\Agent;

require_once(__DIR__ . '/../../init/init_ajax.php');

if (!in_array(21, $_SESSION['droits'])) {
    echo json_encode('Accès refusé');
    exit;
}

// CSFR Protection
$CSRFToken = $request->get('CSRFToken');
if ( !isset($_SESSION['oups']['CSRFToken']) or $CSRFToken != $_SESSION['oups']['CSRFToken']) {
    error_log("CSRF Token Exception {$_SERVER['SCRIPT_NAME']}");
    echo json_encode("CSRF Token Exception {$_SERVER['SCRIPT_NAME']}");
    exit;
}

// Selected agents
$list = $request->get('list');
$list = html_entity_decode($list, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$list = json_decode($list);

// Main tab
$actif = $request->get('actif');
$contrat = $request->get('contrat');
$heures_hebdo = $request->get('heures_hebdo');
$heures_travail = $request->get('heures_travail');
$service = $request->get('service');
$statut = $request->get('statut');

// Skills tab
$postes = $request->get('postes');

if ($postes != '-1') {
        $postes = explode(',', $postes);
        $postes = json_encode($postes);
}

// Update DB
$agents = $entityManager->getRepository(Agent::class)->findById($list);

foreach ($agents as $agent) {
    // Main Tab
    if ($actif != '-1') {
        $agent->actif($actif);
    }

    if ($contrat != '-1') {
        $agent->categorie($contrat);
    }

    if ($heures_hebdo != '-1') {
        $agent->heures_hebdo($heures_hebdo);
    }

    if ($heures_travail != '-1') {
        $agent->heures_travail($heures_travail);
    }

    if ($service != '-1') {
        $agent->service($service);
    }

    if ($statut != '-1') {
        $agent->statut($statut);
    }

    // Skills tab
    if ($postes != '-1') {
        $agent->postes($postes);
    }

    $entityManager->persist($agent);

}
$entityManager->flush();

echo json_encode('ok');
