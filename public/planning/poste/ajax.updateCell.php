<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/planning/poste/ajax.updateCell.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet la mise à jour en arrière plan de la base de données (table pl_poste) lors de l'utilisation du menu contextuel de la
page /index pour placer les agents

Cette page est appelée par la function JavaScript "bataille_navale" utilisé par le fichier planning/poste/menudiv.php
*/

use App\Model\Position;
use App\Model\PlanningPositionHistory;
use App\PlanningBiblio\Helper\PlanningPositionHistoryHelper;

require_once(__DIR__ . '/../../../init/init_ajax.php');
require_once(__DIR__ . '/../../include/function.php');
require_once(__DIR__ . '/../../absences/class.absences.php');
require_once(__DIR__ . '/../../activites/class.activites.php');
require_once(__DIR__ . '/../volants/class.volants.php');
require_once('class.planning.php');

//	Initialisation des variables
$ajouter = $request->get('ajouter');
$barrer = $request->get('barrer');
$CSRFToken = $request->get('CSRFToken');
$date = $request->get('date');
$debut = $request->get('debut');
$fin = $request->get('fin');
$griser = $request->get('griser');
$perso_id = $request->get('perso_id');
$perso_id_origine = $request->get('perso_id_origine');
$poste = $request->get('poste');
$site = $request->get('site');
$tout = $request->get('tout');
$logaction = $request->get('logaction');

$ajouter = filter_var($ajouter, FILTER_CALLBACK, array('options' => 'sanitize_on'));
$barrer = filter_var($barrer, FILTER_SANITIZE_NUMBER_INT);
$date = filter_var($date, FILTER_CALLBACK, array('options' => 'sanitize_dateSQL'));
$debut = filter_var($debut, FILTER_CALLBACK, array('options' => 'sanitize_time'));
$fin = filter_var($fin, FILTER_CALLBACK, array('options' => 'sanitize_time'));
$griser = filter_var($griser, FILTER_SANITIZE_NUMBER_INT);
$perso_id_origine = filter_var($perso_id_origine, FILTER_SANITIZE_NUMBER_INT);
$poste = filter_var($poste, FILTER_SANITIZE_NUMBER_INT);
$site = filter_var($site, FILTER_SANITIZE_NUMBER_INT);
$tout = filter_var($tout, FILTER_CALLBACK, array('options' => 'sanitize_on'));
$logaction = filter_var($logaction, FILTER_CALLBACK, array('options' => 'sanitize_on'));

$login_id = $_SESSION['login_id'];
$now = date("Y-m-d H:i:s");

$barrer = intval($barrer);

// Pärtie 1 : Enregistrement des nouveaux éléments

// Suppression ou marquage absent
if (is_numeric($perso_id) and $perso_id == 0) {
    // Tout barrer
    if ($barrer == 1 and $tout) {

        // History
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->cross($date, $debut, $fin, $site, $poste, $login_id);
        }

        $set=array("absent"=>"1", "chgt_login"=>$login_id, "chgt_time"=>$now);
        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("pl_poste", $set, $where);

    // Barrer l'agent sélectionné
    } elseif ($barrer == 1) {

        // History
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->cross($date, $debut, $fin, $site, $poste, $login_id, $perso_id_origine);
        }

        $set=array("absent"=>"1", "chgt_login"=>$login_id, "chgt_time"=>$now);
        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("pl_poste", $set, $where);
    } elseif ($barrer == -1) {
        $set=array("absent"=>"0", "chgt_login"=>$login_id, "chgt_time"=>$now);
        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("pl_poste", $set, $where);
    }
    // Tout supprimer
    elseif ($tout) {

        // History
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->delete($date, $debut, $fin, $site, $poste, $login_id);
        }

        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste", $where);
    // Supprimer l'agent sélectionné
    // FIXME à vérifier. Pas de suppression si on dégrise.
    } elseif ($griser != -1) {

        // History
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->delete($date, $debut, $fin, $site, $poste, $login_id, $perso_id_origine);
        }

        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste", $where);
    }
}
// Remplacement
else {
    // si ni barrer, ni ajouter : on remplace
    if ($barrer == 0 and !$ajouter) {

        // History
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->put($date, $debut, $fin, $site, $poste, $login_id, $perso_id);
        }

        // Suppression des anciens éléments
        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=> $perso_id_origine);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste", $where);

        if (is_numeric($perso_id)) {
            // Insertion des nouveaux éléments
            $p = new planning();
            $p->update_cell_add_agents($date, $debut, $fin, $poste, $site, $perso_id, $login_id, $CSRFToken);
        } else {
            $tab = json_decode($perso_id);
            if (is_array($tab) and !empty($tab)) {
                foreach ($tab as $elem) {
                    // Insertion des nouveaux éléments
                    $p = new planning();
                    $p->update_cell_add_agents($date, $debut, $fin, $poste, $site, $elem, $login_id, $CSRFToken);
                }
            }
        }
    }
    // Si barrer : on barre l'ancien et ajoute le nouveau
    elseif ($barrer == 1) {
        // On barre l'ancien
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->cross($date, $debut, $fin, $site, $poste, $login_id, $perso_id_origine);
        }
        $set=array("absent"=>"1", "chgt_login"=>$login_id, "chgt_time"=>$now);
        $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("pl_poste", $set, $where);
    
        // On ajoute le nouveau
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->add($date, $debut, $fin, $site, $poste, $login_id, $perso_id, true);
        }
        $insert=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id,
      "chgt_login"=>$login_id, "chgt_time"=>$now);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->insert("pl_poste", $insert);
    }
    // Si Ajouter, on garde l'ancien et ajoute le nouveau
    elseif ($ajouter) {
        if ($logaction) {
            $history = new PlanningPositionHistoryHelper();
            $history->add($date, $debut, $fin, $site, $poste, $login_id, $perso_id);
        }

        $insert=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id,
      "chgt_login"=>$login_id, "chgt_time"=>$now);
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->insert("pl_poste", $insert);
    }
}

// Griser les cellule
if ($griser == 1) {

    // History
    if ($logaction) {
        $history = new PlanningPositionHistoryHelper();
        $history->disable($date, $debut, $fin, $site, $poste, $login_id, $perso_id_origine);
    }

    $insert=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>'0', "grise"=>'1', "chgt_login"=>$login_id, "chgt_time"=>$now);
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("pl_poste", $insert);
} elseif ($griser == -1) {
    $delete=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>'0', "grise"=>'1');
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("pl_poste", $delete);
}


// Disable redo actions
if ($logaction) {
    $entityManager->getRepository(PlanningPositionHistory::class)
        ->archive($date, $site, true);
}

// Partie 2 : Récupération de l'ensemble des éléments
// Et transmission à la fonction JS bataille_navale pour mise à jour de l'affichage de la cellule

$db->selectLeftJoin(
  array("pl_poste","perso_id"),
  array("personnel","id"),
  array("absent","supprime","grise"),
  array("nom","prenom","statut","service","postes",array("name"=>"id","as"=>"perso_id")),
  array("date"=>$date, "debut"=>$debut, "fin"=> $fin, "poste"=>$poste, "site"=>$site),
  array(),
  "ORDER BY nom,prenom"
);

$response = array(
    'tab' => null,
    'undoable' => 1,
    'redoable' => 1
);

$undoables = $entityManager
     ->getRepository(PlanningPositionHistory::class)
     ->undoable($date, $site);
$redoables = $entityManager
     ->getRepository(PlanningPositionHistory::class)
     ->redoable($date, $site);

if (empty($undoables)) {
    $response['undoable'] = 0;
}

if (empty($redoables)) {
    $response['redoable'] = 0;
}

if (!$db->result) {
    echo json_encode($response);
    return;
}

if ($db->result[0]['grise'] == 1) {
    $response['tab'] = 'grise';
    echo json_encode($response);
    return;
}

$tab=$db->result;
usort($tab, "cmp_nom_prenom");

// Ajoute les qualifications de chaque agent (activités) dans le tableaux $cellules pour personnaliser l'affichage des cellules en fonction des qualifications
$a=new activites();
$a->deleted=true;
$a->fetch();
$activites=$a->elements;

foreach ($tab as $k => $v) {
    if ($v['postes']) {
        $p = json_decode(html_entity_decode($v['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        $tab[$k]['activites'] = array();
        foreach ($activites as $elem) {
            if (in_array($elem['id'], $p)) {
                $tab[$k]['activites'][] = 'activite_'.strtolower(removeAccents(str_replace(array('/',' ',), '_', $elem['nom'])));
            }
        }
        $tab[$k]['activites'] = implode(' ', $tab[$k]['activites']);
    }
}


// Recherche des sans repas en dehors de la boucle pour optimiser les performances (juillet 2016)
$p = new planning();
$sansRepas = $p->sansRepas($date, $debut, $fin, $poste);

// Recherche des absences

$p = $entityManager->getRepository(Position::class)->find($poste);

$a=new absences();
$a->valide=false;
$a->rejected = false;
$a->teleworking = !$p->teleworking();
$a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date.' '.$debut, $date.' '.$fin);

$absences=$a->elements;

// Recherche des agents volants
if ($config['Planning-agents-volants']) {
    $v = new volants($date);
    $v->fetch($date);
    $agents_volants = $v->selected;
}

for ($i=0;$i<count($tab);$i++) {

  // Distinction des agents volants
    if ($config['Planning-agents-volants'] and in_array($tab[$i]['perso_id'], $agents_volants)) {
        $tab[$i]["statut"] = 'volants';
    }

    // Mise en forme des statut et service pour affectation des classes css
    $tab[$i]["statut"]=removeAccents($tab[$i]["statut"]);
    $tab[$i]["service"]=removeAccents($tab[$i]["service"]);

    // Color the logged in agent.
    $tab[$i]['color'] = null;
    if (!empty($config['Affichage-Agent']) and $tab[$i]['perso_id'] == $_SESSION['login_id']) {
      $tab[$i]['color'] = filter_var($config['Affichage-Agent'], FILTER_CALLBACK, ['options' => 'sanitize_color']);
    }

    // Ajout des Sans Repas (SR)
    if ($sansRepas === true or in_array($tab[$i]['perso_id'], $sansRepas)) {
        $tab[$i]["sr"] = 1;
    } else {
        $tab[$i]["sr"] = 0;
    }
  
    // Marquage des absences de la table absences
    foreach ($absences as $absence) {
        if ($absence["perso_id"] == $tab[$i]['perso_id'] and $absence['debut'] < $date." ".$fin and $absence['fin'] > $date." ".$debut) {

            if (($config['Absences-Exclusion'] == 1 and $absence['valide'] == 99999)
                or $config['Absences-Exclusion'] == 2) {
                // Nothing changes. If absent = 1 because manually crossed out, absent must remain equal to 1.
            } elseif ($absence['valide'] > 0 or $config['Absences-validation'] == 0) {
                $tab[$i]['absent'] = 1;
                break;  // Garder le break à cet endroit pour que les absences validées prennent le dessus sur les non-validées
            } elseif ($config['Absences-non-validees'] and $tab[$i]['absent'] != 1) {
                $tab[$i]['absent'] = 2;
            }
        }
    }
}

// Marquage des congés
if ($config['Conges-Enable']) {
    include "../../conges/ajax.planning.updateCell.php";
}

$response['tab'] = $tab;

echo json_encode($response);

/*
Résultat :
  [0] => Array (
    [nom] => Nom
    [prenom] => Prénom
    [statut] => Statut
    [service] => Service
    [color] => Color
    [activites] => activite_activite1 activite_activite2 (activités de l'agents précédées de activite_ et séparées par des espaces, pour appliquer les classes .activite_xxx)
    [perso_id] => 86
    [absent] => 0/1/2 ( 0 = pas d'absence ; 1 = absence validée ; 2 = absence non validée )
    [supprime] => 0/1
    [sr] =>0/1
    )
  [1] => Array (
    ...
*/
