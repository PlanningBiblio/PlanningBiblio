<?php
/**
Planning Biblio, Version 2.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/index.php
Création : 23 juillet 2013
Dernière modification : 4 mai 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des plannings de présence pour l'administrateur
Page accessible à partir du menu administration/planning de présence
*/

require_once "class.planningHebdo.php";
require_once "personnel/class.personnel.php";

// Initialisation des variables
$debut=filter_input(INPUT_GET, "debut", FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET, "fin", FILTER_SANITIZE_STRING);
$reset=filter_input(INPUT_GET, "reset", FILTER_SANITIZE_STRING);

$debut=filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$reset=filter_var($reset, FILTER_CALLBACK, array("options"=>"sanitize_on"));

if (!$debut) {
    $debut=array_key_exists("planningHebdoDebut", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoDebut']:null;
}

if (!$fin) {
    $fin=array_key_exists("planningHebdoFin", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoFin']:null;
}

if ($reset) {
    $debut=null;
    $fin=null;
}
$_SESSION['oups']['planningHebdoDebut']=$debut;
$_SESSION['oups']['planningHebdoFin']=$fin;
$message=null;

// Droits d'administration
// Seront utilisés pour n'afficher que les agents gérés si l'option "PlanningHebdo-notifications-agent-par-agent" est cochée
$adminN1 = in_array(1101, $droits);
$adminN2 = in_array(1201, $droits);

// Droits de gestion des plannings de présence agent par agent
if ($adminN1 and $config['PlanningHebdo-notifications-agent-par-agent']) {
    $db = new db();
    $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));

    if (!$adminN2) {
        $perso_ids = array($_SESSION['login_id']);
    
        if ($db->result) {
            foreach ($db->result as $elem) {
                $perso_ids[] = $elem['perso_id'];
            }
        }
    }
}

// Recherche des plannings
$p=new planningHebdo();
$p->debut=dateFr($debut);
$p->fin=dateFr($fin);
if (!empty($perso_ids)) {
    $p->perso_ids = $perso_ids;
}
$p->fetch();

$a = new personnel();
$a->supprime = array(0,1,2);
$a->fetch();
$agents = $a->elements;

echo "<h3>Plannings de présence</h3>\n";

echo <<<EOD
<div id='buttons'>
<form name='form' method='get' action='index.php'>
<input type='hidden' name='page' value='planningHebdo/index.php' />
Début : <input type='text' name='debut' class='datepicker' value='$debut' />
&nbsp;&nbsp;Fin : <input type='text' name='fin' class='datepicker' value='$fin' />
&nbsp;&nbsp;<input type='submit' value='OK' class='ui-button' />
&nbsp;&nbsp;<input type='button' value='Effacer' onclick='location.href="index.php?page=planningHebdo/index.php&amp;reset=on"' class='ui-button' />
<a class='ui-button' href='index.php?page=planningHebdo/modif.php&amp;retour=index.php' style='position:absolute; right:10px;'>Entrer un nouveau planning</a>
</div>
</form>

<br/>

<table id='tablePlanningHebdo' class='CJDataTable' data-sort='[[3],[4],[1]]'>
<thead>
  <tr>
    <th rowspan='2' class='dataTableNoSort'>&nbsp;</th>
    <th rowspan='2' >Agent</th>
    <th rowspan='2' >Service</th>
    <th rowspan='2' class='dataTableDateFR'>Début</th>
    <th rowspan='2' class='dataTableDateFR'>Fin</th>
    <th rowspan='2' class='dataTableDateFR'>Saisie</th>
    <th colspan='2' >Validation</th>
    <th rowspan='2' >Actuel</th>
    <th rowspan='2' >Commentaires</th>
  </tr>
  <tr>
    <th>&Eacute;tat</th>
    <th class='dataTableDateFR'>Date</th>
  </tr>
</thead>
<tbody>
EOD;
foreach ($p->elements as $elem) {
    $actuel=$elem['actuel']?"Oui":null;

    // Validation
    $validation_class = 'bold';
    $validation_date = dateFr($elem['saisie'], true);
    $validation = 'Demand&eacute;';

    if ($elem['valide_n1'] > 0) {
        $validation_class = 'bold';
        $validation_date = dateFr($elem['validation_n1'], true);
        $validation = $lang['work_hours_dropdown_accepted_pending'];
        // 99999 : ID cron : donc pas de nom a afficher
        if ($elem['valide_n1'] != 99999) {
            $validation.=", ".nom($elem['valide'], 'nom p', $agents);
        }
    } elseif ($elem['valide_n1'] < 0) {
        $validation_class = 'bold';
        $validation_date = dateFr($elem['validation_n1'], true);
        $validation = $lang['work_hours_dropdown_refused_pending'];
        // 99999 : ID cron : donc pas de nom a afficher
        if ($elem['valide_n1'] != 99999) {
            $validation.=", ".nom(-$elem['valide'], 'nom p', $agents);
        }
    }

    if ($elem['valide'] > 0) {
        $validation_class = null;
        $validation_date = dateFr($elem['validation'], true);
        $validation = $lang['work_hours_dropdown_accepted'];
        // 99999 : ID cron : donc pas de nom a afficher
        if ($elem['valide'] != 99999) {
            $validation.=", ".nom($elem['valide'], 'nom p', $agents);
        }
    } elseif ($elem['valide'] < 0) {
        $validation_class = 'red';
        $validation_date = dateFr($elem['validation'], true);
        $validation = $lang['work_hours_dropdown_refused'];
        // 99999 : ID cron : donc pas de nom a afficher
        if ($elem['valide'] != 99999) {
            $validation.=", ".nom(-$elem['valide'], 'nom p', $agents);
        }
    }
 

    $planningRemplace=$elem['remplace']==0?dateFr($elem['saisie'], true):$planningRemplace;
    $commentaires=$elem['remplace']?"Remplace le planning <br/>du $planningRemplace":null;
    $arrow=$elem['remplace']?"<font style='font-size:20pt;'>&rdsh;</font>":null;

    echo "<tr id='tr_{$elem['id']}'>";
    echo "<td style='white-space:nowrap;'>$arrow \n";
    echo "<a href='index.php?page=planningHebdo/modif.php&amp;id={$elem['id']}&amp;retour=index.php'/>";
    echo "<span class='pl-icon pl-icon-edit' title='Voir'></span></a>";
    
    // Si le champ "clé" est renseigné : importation automatique, donc on n'affiche pas les icônes copie et suppression
    if (!$elem['cle']) {
        echo "<a href='index.php?page=planningHebdo/modif.php&amp;copy={$elem['id']}&amp;retour=index.php'/>";
        echo "<span class='pl-icon pl-icon-copy' title='Copier'></span></a>";
        echo "<a href='javascript:plHebdoSupprime({$elem['id']});' style='margin-left:6px;'/>";
        echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a></td>";
    }
    
    echo "<td>{$elem['nom']}</td>";
    echo "<td>{$elem['service']}</td>";
    echo "<td>".dateFr($elem['debut'])."</td>";
    echo "<td>".dateFr($elem['fin'])."</td>";
    echo "<td>".dateFr($elem['saisie'], true)."</td>";
    echo "<td class='$validation_class' >$validation</td>";
    echo "<td class='$validation_class' >$validation_date</td>";
    echo "<td>$actuel</td>";
    echo "<td>$commentaires</td>";
    echo "</tr>\n";
}
echo "</tbody></table>\n";
