<?php
/**
Planning Biblio, Plugin Congés Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/voir.php
Création : 24 juillet 2013
Dernière modification : 25 juillet 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant voir les congés
Accessible par le menu congés/Voir les congés ou par la page conges/index.php
Inclus dans le fichier index.php
*/

require_once "class.conges.php";
require_once "personnel/class.personnel.php";

// Initialisation des variables
// Gestion des congés niveaux 1 et 2
$admin=in_array(7, $droits)?true:false;
$admin=in_array(2, $droits)?true:$admin;

$annee=filter_input(INPUT_GET, "annee", FILTER_SANITIZE_STRING);
$congesAffiches=filter_input(INPUT_GET, "congesAffiches", FILTER_SANITIZE_STRING);
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);
$reset=filter_input(INPUT_GET, "reset", FILTER_CALLBACK, array("options"=>"sanitize_on"));
$supprimes=filter_input(INPUT_GET, "supprimes", FILTER_CALLBACK, array("options"=>"sanitize_on"));

if ($admin and $perso_id==null) {
    $perso_id=isset($_SESSION['oups']['conges_perso_id'])?$_SESSION['oups']['conges_perso_id']:$_SESSION['login_id'];
} elseif ($perso_id==null) {
    $perso_id=$_SESSION['login_id'];
}

$agents_supprimes=isset($_SESSION['oups']['conges_agents_supprimes'])?$_SESSION['oups']['conges_agents_supprimes']:false;
$agents_supprimes=($annee and $supprimes)?true:$agents_supprimes;
$agents_supprimes=($annee and !$supprimes)?false:$agents_supprimes;

if (!$annee) {
    $annee=isset($_SESSION['oups']['conges_annee'])?$_SESSION['oups']['conges_annee']:(date("m")<9?date("Y")-1:date("Y"));
}

if (!$congesAffiches) {
    $congesAffiches=isset($_SESSION['oups']['congesAffiches'])?$_SESSION['oups']['congesAffiches']:"aVenir";
}

if ($reset) {
    $annee=date("m")<9?date("Y")-1:date("Y");
    $perso_id=$_SESSION['login_id'];
    $agents_supprimes=false;
}
$_SESSION['oups']['conges_annee']=$annee;
$_SESSION['oups']['congesAffiches']=$congesAffiches;
$_SESSION['oups']['conges_perso_id']=$perso_id;
$_SESSION['oups']['conges_agents_supprimes']=$agents_supprimes;


$debut=$annee."-09-01";
$fin=($annee+1)."-08-31";

if ($congesAffiches=="aVenir") {
    $debut=date("Y-m-d");
}

$c=new conges();
$c->debut=$debut;
$c->fin=$fin." 23:59:59";
if ($perso_id!=0) {
    $c->perso_id=$perso_id;
}
if ($agents_supprimes) {
    $c->agents_supprimes=array(0,1);
}
$c->fetch();

// Recherche des agents pour le menu
if ($admin) {
    $p=new personnel();
    if ($agents_supprimes) {
        $p->supprime=array(0,1);
    }
    $p->fetch();
    $agents_menu=$p->elements;
}

// Recherche des agents pour la fonction nom()
$p=new personnel();
$p->supprime=array(0,1,2);
$p->fetch();
$agents=$p->elements;

// Années universitaires
$annees=array();
for ($d=date("Y")+2;$d>date("Y")-11;$d--) {
    $annees[]=array($d,$d."-".($d+1));
}

// Affichage du tableau
echo "<h3 class='noprint'>Liste des congés</h3>\n";
echo "<h3 class='print_only'>Liste des congés de ".nom($perso_id, "prenom nom", $agents).", année $annee-".($annee+1)."</h3>\n";
echo <<<EOD
<form name='form' method='get' action='index.php' class='noprint'>
<input type='hidden' name='page' value='conges/voir.php' />
<table class='tableauStandard'><tbody><tr>
<td>Ann&eacute;e : <select name='annee'>
EOD;
foreach ($annees as $elem) {
    $selected=$annee==$elem[0]?"selected='selected'":null;
    echo "<option value='{$elem[0]}' $selected >{$elem[1]}</option>";
}
echo "</select></td>\n";

$selected=$congesAffiches=="aVenir"?"selected='selected'":null;
echo "<td>Congés : ";
echo "<select name='congesAffiches'>";
echo "<option value='tous'>Tous</option>";
echo "<option value='aVenir' $selected>A venir</option>";
echo "</select></td>\n";

if ($admin) {
    echo "<td style='text-align:left;'>\n";
    echo "<span style='padding:5px;'>Agents : ";
    echo "<select name='perso_id' id='perso_id'>";
    $selected=$perso_id==0?"selected='selected'":null;
    echo "<option value='0' $selected >Tous</option>";
    foreach ($agents_menu as $agent) {
        $selected=$agent['id']==$perso_id?"selected='selected'":null;
        echo "<option value='{$agent['id']}' $selected >{$agent['nom']} {$agent['prenom']}</option>";
    }
    echo "</select>\n";
    echo "</span>\n";

    $checked=$agents_supprimes?"checked='checked'":null;

    echo "<br/>\n";
    echo "<span style='padding:5px;'>Agents supprim&eacute;s : ";
    echo "<input type='checkbox' $checked name='supprimes' onclick='updateAgentsList(this,\"perso_id\");'/>\n";
    echo "</span>\n";
    echo "</td>\n";
}
echo <<<EOD
<td><input type='submit' value='OK' id='button-OK' class='ui-button'/></td>
<td><input type='button' value='Effacer' onclick='location.href="index.php?page=conges/voir.php&reset=on"' class='ui-button'/></td>
</tr></tbody></table>
</form>
<br/>
<table id='tableConges' class='CJDataTable' data-sort='[[1],[2]]'>
<thead>
  <tr>
    <th rowspan='2' class='dataTableNoSort'>&nbsp;</th>
    <th rowspan='2' class='dataTableDateFR'>Début</th>
    <th rowspan='2' class='dataTableDateFR-fin'>Fin</th>
EOD;
if ($admin) {
    echo "<th rowspan='2'>Nom</th>";
}
echo "<th colspan='2' class='ui-state-default'>Validation</th>\n";
echo "<th rowspan='2'>Heures</th>";
echo "<th rowspan='2'>Crédits</th><th rowspan='2'>Reliquat</th><th rowspan='2'>Récupérations</th><th rowspan='2'>Solde Débiteur</th></tr>\n";
echo "<tr><th>&Eacute;tat</th><th class='dataTableDateFR'>Date</th></tr></thead>\n";
echo "<tbody>\n";

foreach ($c->elements as $elem) {
    $debut=str_replace("00h00", "", dateFr($elem['debut'], true));
    $fin=str_replace("23h59", "", dateFr($elem['fin'], true));
    $heures=heure4($elem['heures']);
    $validation="Demand&eacute;, ".dateFr($elem['saisie'], true);
    $validationDate=dateFr($elem['saisie'], true);
    $validationStyle="font-weight:bold;";

    $credits=null;
    $reliquat=null;
    $recuperations=null;
    $anticipation=null;
    $creditClass=null;
    $reliquatClass=null;
    $recuperationsClass=null;
    $anticipationClass=null;

    if ($elem['saisie_par'] and $elem['perso_id']!=$elem['saisie_par']) {
        $validation.=" par ".nom($elem['saisie_par'], 'nom p', $agents);
    }

    if ($elem['valide']<0) {
        $validation="Refus&eacute;, ".nom(-$elem['valide'], 'nom p', $agents);
        $validationDate=dateFr($elem['validation'], true);
        $validationStyle="color:red;";
    } elseif ($elem['valide'] or $elem['information']) {
        $validation="Valid&eacute;, ".nom($elem['valide'], 'nom p', $agents);
        $validationDate=dateFr($elem['validation'], true);
        $validationStyle=null;

        $credits=heure4($elem['solde_prec']);
        $creditClass="aRight ";
        if ($elem['solde_prec']!=$elem['solde_actuel']) {
            $credits=heure4($elem['solde_prec'], true)." &rarr; ".heure4($elem['solde_actuel'], true);
            $creditClass.="bold";
        }

        $recuperations=heure4($elem['recup_prec']);
        $recuperationsClass="aRight ";
        if ($elem['recup_prec']!=$elem['recup_actuel']) {
            $recuperations=heure4($elem['recup_prec'], true)." &rarr; ".heure4($elem['recup_actuel'], true);
            $recuperationsClass.="bold";
        }

        $reliquat=heure4($elem['reliquat_prec']);
        $reliquatClass="aRight ";
        if ($elem['reliquat_prec']!=$elem['reliquat_actuel']) {
            $reliquat=heure4($elem['reliquat_prec'], true)." &rarr; ".heure4($elem['reliquat_actuel'], true);
            $reliquatClass.="bold";
        }

        $anticipation=heure4($elem['anticipation_prec']);
        $anticipationClass="aRight ";
        if ($elem['anticipation_prec']!=$elem['anticipation_actuel']) {
            $anticipation=heure4($elem['anticipation_prec'], true)." &rarr; ".heure4($elem['anticipation_actuel'], true);
            $anticipationClass.="bold";
        }
    } elseif ($elem['valide_n1']) {
        $validation="En attente de validation hi&eacute;rarchique";
        $validationDate=dateFr($elem['validation_n1'], true);
        $validationStyle="font-weight:bold;";
    }
    if ($elem['information']) {
        $nom=$elem['information']<999999999?nom($elem['information'], 'nom p', $agents).", ":null;	// >999999999 = cron
        $validation="Mise à jour des cr&eacute;dits, $nom";
        $validationDate=dateFr($elem['infoDate'], true);
        $validationStyle=null;
    } elseif ($elem['supprime']) {
        $validation="Supprim&eacute;, ".nom($elem['supprime'], 'nom p', $agents);
        $validationDate=dateFr($elem['suppr_date'], true);
        $validationStyle=null;
    }

    $nom=$admin?"<td>".nom($elem['perso_id'], 'nom p', $agents)."</td>":null;
  
    echo "<tr><td>";
    if ($elem['supprime'] or $elem['information']) {
        echo "&nbsp;";
    } else {
        echo "<a href='index.php?page=conges/modif.php&amp;id={$elem['id']}'/>";
        echo "<span class='pl-icon pl-icon-edit' title='Voir'></span></a>";
    }
    echo "</td>";
    echo "<td>$debut</td><td>$fin</td>$nom<td style='$validationStyle'>$validation</td><td>$validationDate</td>\n";
    echo "<td class='aRight'>$heures</td>";
    echo "<td class='$creditClass'>$credits</td><td class='$reliquatClass'>$reliquat</td>";
    echo "<td class='$recuperationsClass'>$recuperations</td><td class='$anticipationClass'>$anticipation</td></tr>\n";
}

?>
</tbody>
</table>