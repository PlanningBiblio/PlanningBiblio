<?php
/**
Planno.
Export JSON for Koha
*/

$version = 'ajax';

require_once __DIR__ . '/init_ajax.php';
require_once __DIR__ . '/planning/poste/class.planning.php';
include_once __DIR__ . '/absences/class.absences.php';
include_once __DIR__ . '/conges/class.conges.php';
include_once __DIR__ . '/personnel/class.personnel.php';
include_once __DIR__ . '/planning/poste/fonctions.php';

use App\Model\AbsenceReason;
use App\Model\SelectFloor;
use App\PlanningBiblio\PresentSet;
use App\PlanningBiblio\Framework;

// Initialisation des variables
$site = filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);
$date = filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);

// Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
$date=filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));

$verrou=false;
//		------------------		DATE		-----------------------//
if (!$date and array_key_exists('PLdate', $_SESSION)) {
    $date=$_SESSION['PLdate'];
} elseif (!$date and !array_key_exists('PLdate', $_SESSION)) {
    $date=date("Y-m-d");
}

//		------------------		FIN DATE		-----------------------//
//		------------------		TABLEAU		-----------------------//

// Multisites : la variable $site est égale à 1 par défaut.
// Elle prend la valeur GET['site'] si elle existe, sinon la valeur de la SESSION ['site']
// En dernier lieu, la valeur du site renseignée dans la fiche de l'agent
if (!$site and array_key_exists("site", $_SESSION['oups'])) {
    $site=$_SESSION['oups']['site'];
}
if (!$site) {
    $p=new personnel();
    $p->fetchById($_SESSION['login_id']);
    $site = isset($p->elements[0]['sites'][0]) ? $p->elements[0]['sites'][0] : null;
}
$site=$site?$site:1;
$_SESSION['oups']['site']=$site;

//		------------------		FIN TABLEAU		-----------------------//
global $idCellule;
$idCellule=0;


    
//-----------------------------			Verrouillage du planning			-----------------------//
$db=new db();
$db->select2("pl_poste_verrou", "*", array("date"=>$date, "site"=>$site));
if ($db->result) {
    $verrou = $db->result[0]['verrou2'];
    $validation_date = $db->result[0]['validation2'];
}
//	---------------		FIN changement de couleur du menu et de la periode en fonction du jour sélectionné	--------------------------//



//	-----------------------		FIN Récupération des postes	-----------------------------//

$db=new db();
$db->select2("pl_poste_tab_affect", "tableau", array("date"=>$date, "site"=>$site));

if (isset($db->result[0]['tableau'])) {
    $tab=$db->result[0]['tableau'];
}


if (!$verrou) {
    return '[]';
}

//--------------	Recherche des infos cellules	------------//
// Toutes les infos seront stockées danx un tableau et utilisées par les fonctions cellules_postes
$db=new db();
$db->selectLeftJoin(
    array("pl_poste","perso_id"),
    array("personnel","id"),
    array("perso_id","debut","fin","poste","absent","supprime","grise"),
    array("nom","prenom","statut","service","postes", 'depart'),
    array("date"=>$date, "site"=>$site),
    array(),
    "ORDER BY `{$dbprefix}personnel`.`nom`, `{$dbprefix}personnel`.`prenom`"
);
// $cellules will be used in the cellule_poste function. Using a global variable will avoid multiple access to the database and enhance performances
global $cellules;
$cellules=$db->result?$db->result:array();
usort($cellules, "cmp_nom_prenom");

// Recherche des agents volants
if ($config['Planning-agents-volants']) {
    $v = new volants($date);
    $v->fetch($date);
    $agents_volants = $v->selected;

    // Modification du statut pour les agents volants afin de personnaliser l'affichage
    foreach ($cellules as $k => $v) {
        if (in_array($v['perso_id'], $agents_volants)) {
            $cellules[$k]['statut'] = 'volants';
        }
    }
}

// $absence_reasons will be used in the cellule_poste function. Using a global variable will avoid multiple access to the database and enhance performances
global $absence_reasons;
$absence_reasons = $entityManager->getRepository(AbsenceReason::class);

// Recherche des absences
// Le tableau $absences sera utilisé par la fonction cellule_poste pour barrer les absents dans le plannings et pour afficher les absents en bas du planning
// $absences will be used in the cellule_poste function. Using a global variable will avoid multiple access to the database and enhance performances
$a=new absences();
$a->valide=false;
$a->rejected = false;
$a->agents_supprimes = array(0,1,2);    // required for history
$a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
$absences=$a->elements;
global $absences;

// Tri des absences par nom
usort($absences, "cmp_nom_prenom_debut_fin");

// Affichage des absences en bas du planning : absences concernant le site choisi
$a=new absences();
$a->valide=false;
$a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date, array($site));
$absences_planning = $a->elements;

// Informations sur les congés
// $conges will be used in the cellule_poste function. Using a global variable will avoid multiple access to the database and enhance performances
$conges = array();
global $conges;
if ($config['Conges-Enable']) {
    $c = new conges();
    $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
}
//--------------	FIN Recherche des infos cellules	------------//


//	------------		Affichage du tableau			--------------------//
//	Lignes de separation
$db=new db();
$db->select2("lignes");
if ($db->result) {
    foreach ($db->result as $elem) {
        $lignes_sep[$elem['id']]=$elem['nom'];
    }
}

// Récupération de la structure du tableau
$t = new Framework();
$t->id=$tab;
$t->get();
$tabs=$t->elements;

// Repère les heures de début et de fin de chaque tableau pour ajouter des colonnes si ces heures sont différentes
$debut="23:59";
$fin=null;
foreach ($tabs as $elem) {
    $debut=$elem["horaires"][0]["debut"]<$debut?$elem["horaires"][0]["debut"]:$debut;
    $nb=count($elem["horaires"])-1;
    $fin=$elem["horaires"][$nb]["fin"]>$fin?$elem["horaires"][$nb]["fin"]:$fin;
}

// affichage du tableau :

// Tableaux masqués
$hiddenTables = array();
$db=new db();
$db->select2("hidden_tables", "*", array("perso_id"=>$_SESSION['login_id'],"tableau"=>$tab));
if ($db->result) {
    $hiddenTables = json_decode(html_entity_decode($db->result[0]['hidden_tables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
}

// $sn : index des tableaux Sans nom pour l'affichage des tableaux masqués
$sn=1;

$j=0;

$json_cells = [];

foreach ($tabs as $tab) {
    $hiddenTable = in_array($j, $hiddenTables) ? 'hidden-table' :null;

    // Comble les horaires laissés vides : créé la colonne manquante, les cellules de cette colonne seront grisées
    $cellules_grises=array();
    $tmp=array();

    // Première colonne : si le début de ce tableau est supérieur au début d'un autre tableau
    $k=0;
    if ($tab['horaires'][0]['debut']>$debut) {
        $tmp[]=array("debut"=>$debut, "fin"=>$tab['horaires'][0]['debut']);
        $cellules_grises[]=$k++;
    }

    // Colonnes manquantes entre le début et la fin
    foreach ($tab['horaires'] as $key => $value) {
        if ($key==0 or $value["debut"]==$tab['horaires'][$key-1]["fin"]) {
            $tmp[]=$value;
        } elseif ($value["debut"]>$tab['horaires'][$key-1]["fin"]) {
            $tmp[]=array("debut"=>$tab['horaires'][$key-1]["fin"], "fin"=>$value["debut"]);
            $tmp[]=$value;
            $cellules_grises[]=$k++;
        }
        $k++;
    }

    // Dernière colonne : si la fin de ce tableau est inférieure à la fin d'un autre tableau
    $nb=count($tab['horaires'])-1;
    if ($tab['horaires'][$nb]['fin']<$fin) {
        $tmp[]=array("debut"=>$tab['horaires'][$nb]['fin'], "fin"=>$fin);
        $cellules_grises[]=$k;
    }

    $tab['horaires']=$tmp;

    //	Lignes postes et grandes lignes
    foreach ($tab['lignes'] as $ligne) {

        // Lignes postes
        if ($ligne['type']=="poste" and $ligne['poste']) {
            // Affichage de la ligne
            $i=1;
            $k=1;
            foreach ($tab['horaires'] as $horaires) {
                // Recherche des infos à afficher dans chaque cellule
                // Cellules grisées si définies dans la configuration du tableau et si la colonne a été ajoutée automatiquement
                if (!in_array("{$ligne['ligne']}_{$k}", $tab['cellules_grises']) and !in_array($i-1, $cellules_grises)) {
                    $json_cell = cellule_poste($date, $horaires["debut"], $horaires["fin"], nb30($horaires['debut'], $horaires['fin']), "noms", $ligne['poste'], $site, true);
                    if (!empty($json_cell['agents'])) {
                        $json_cells[] = $json_cell;
                    }
                }
                $i++;
                $k++;
            }
        }
    }
    $j++;
}

echo json_encode($json_cells);
exit;