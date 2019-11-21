<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/infos.php
Création : mai 2011
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'ajouter une information relative à la gestion des absences : Formulaire, confirmation, validation

Page appelée par la page index.php
*/

require_once "class.absences.php";

//	Initialisation des variables
$id = $request->get('id');
$op = $request->get('op');
$debut = $request->get('debut');
$fin = $request->get('fin');
$suppression = $request->get('suppression');
$validation = $request->get('validation');
$texte = trim($request->get('texte'));
$CSRFToken = trim($request->get('CSRFToken'));

// Contrôle sanitize_dateFr en 2 temps pour éviter les erreurs CheckMarx
$debut=filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);

if ($op == 'save') {
    $templates_params['save'] = 1;
}

$templates_params['id'] = $id;
$templates_params['debut'] = $debut;
$templates_params['fin'] = $fin;
$templates_params['texte'] = $texte;
$templates_params['suppression'] = $suppression;
$templates_params['validation'] = $validation;

//			----------------		Suppression							-------------------------------//
if ($suppression and $validation) {
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("absences_infos", array("id"=>$id));
} elseif ($suppression) {
}
//			----------------		FIN Suppression							-------------------------------//
//			----------------		Validation du formulaire							-------------------------------//
elseif ($validation) {		//		Validation
    if ($id) {
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("absences_infos", array("debut"=>$debutSQL,"fin"=>$finSQL,"texte"=>$texte), array("id"=>$id));
    } else {
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->insert("absences_infos", array("debut"=>$debutSQL,"fin"=>$finSQL,"texte"=>$texte));
    }
} elseif ($debut) {		//		Vérification
    $fin=$fin?$fin:$debut;
    $templates_params['fin'] = $fin;
}
//			----------------		FIN Validation du formulaire							-------------------------------//
else {
    if ($id) {
        $db=new db();
        $db->select2("absences_infos", "*", array("id"=>$id));
        $debut=dateFr3($db->result[0]['debut']);
        $fin=dateFr3($db->result[0]['fin']);
        $texte = html_entity_decode($db->result[0]['texte'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
    } else {
        $debut=null;
        $fin=null;
        $texte=null;
    }

    $templates_params['debut'] = $debut;
    $templates_params['fin'] = $fin;
    $templates_params['texte'] = $texte;
}

$template = $twig->load('absences/infos.html.twig');
echo $template->render($templates_params);
exit;
