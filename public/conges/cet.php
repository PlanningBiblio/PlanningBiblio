<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/cet.php
Création : 6 mars 2014
Dernière modification : 16 février 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de voir les CET
*/

include_once "class.conges.php";
include_once(__DIR__.'/../personnel/class.personnel.php');

// Initialisation des variables
$annee = filter_input(INPUT_GET, 'annee', FILTER_SANITIZE_NUMBER_INT);
$msg = filter_input(INPUT_GET, 'message', FILTER_SANITIZE_STRING);
$perso_id = filter_input(INPUT_GET, 'perso_id', FILTER_SANITIZE_NUMBER_INT);
$reset = filter_input(INPUT_GET, 'reset', FILTER_SANITIZE_NUMBER_INT);

if (!$annee) {
  $annee = !empty($_SESSION['oups']['cet_annee']) ? $_SESSION['oups']['cet_annee'] : date("Y")+1 ;
}

// Droits d'administration niveau 1 et niveau 2
$c = new conges();
$roles = $c->roles($perso_id, true);
list($adminN1, $adminN2) = $roles;

$displayValidation=$adminN1?null:"style='display:none;'";
$displayValidationN2=$adminN2?null:"style='display:none;'";
if ($adminN1) {
  if (!$perso_id) {
    $perso_id = !empty($_SESSION['oups']['cet_perso_id']) ? $_SESSION['oups']['cet_perso_id'] : $_SESSION['login_id'];
  }
} else {
    $perso_id=$_SESSION['login_id'];
}
if ($reset) {
    $annee=date("Y")+1;
    $perso_id=$_SESSION['login_id'];
}
$_SESSION['oups']['cet_annee']=$annee;
$_SESSION['oups']['cet_perso_id']=$perso_id;

$message=null;

// Recherche des demandes de récupérations enregistrées
$c=new conges();
$c->admin=$adminN1;
$c->annee=$annee;
if ($perso_id!=0) {
    $c->perso_id=$perso_id;
}
$c->getCET();
$cet=$c->elements;

// Recherche des agents pour le menu
$p=new personnel();
$p->fetch();
$agents_menu=$p->elements;

// Recherche des agents pour la fonction nom()
$p=new personnel();
$p->supprime=array(0,1,2);
$p->fetch();
$agents=$p->elements;

// Années universitaires
$annees=array();
for ($d=date("Y")+2;$d>date("Y")-11;$d--) {
    $annees[]=array($d,$d);
}

// Notifications
if ($msg) {
    switch ($msg) {
        case "Demande-OK": $message="Votre demande a été enregistrée"; $type="highlight"; break;
        case "Demande-Erreur": $message="Une erreur est survenue lors de l'enregitrement de votre demande."; $type="error"; break;
        case "OK": $message="Vos modifications ont été enregistrées"; $type="highlight"; break;
        case "Erreur": $message="Une erreur est survenue lors de la validation de vos modifications."; $type="error"; break;
        case "Refus": $message="Accès refusé."; $type="error"; break;
    }
    if ($message) {
        echo "<script type='text/JavaScript'>information('$message','$type',70);</script>\n";
    }
}

// Affichage
echo "<h3 class='print_only'>Liste des demandes de CET de ".nom($perso_id, "prenom nom").", année $annee-".($annee+1)."</h3>\n";
echo <<<EOD
<h3 class='noprint'>Compte &Eacute;pargne Temps</h3>

<div id='liste'>
<h4 class='noprint'>Liste des demandes</h4>
<form name='form' method='get' action='index.php' class='noprint'>
<p>
<input type='hidden' name='page' value='conges/cet.php' />
<input type='hidden' id='adminN1' value='$adminN1' />
<input type='hidden' id='adminN2' value='$adminN2' />
Pour l'ann&eacute;e : <select name='annee'>
EOD;
foreach ($annees as $elem) {
    $selected=$annee==$elem[0]?"selected='selected'":null;
    echo "<option value='{$elem[0]}' $selected >{$elem[1]}</option>";
}
echo "</select>\n";

if ($adminN1) {
    echo "&nbsp;&nbsp;Agent : ";
    echo "<select name='perso_id' id='perso_id'>";
    $selected=$perso_id==0?"selected='selected'":null;
    echo "<option value='0' $selected >Tous</option>";
    foreach ($agents_menu as $agent) {
        $selected=$agent['id']==$perso_id?"selected='selected'":null;
        echo "<option value='{$agent['id']}' $selected >{$agent['nom']} {$agent['prenom']}</option>";
    }
    echo "</select>\n";
} else {
    echo "<input type='hidden' name='perso_id' id='perso_id' value='$perso_id' />\n";
}
echo <<<EOD
&nbsp;&nbsp;<input type='submit' value='OK' id='button-OK' class='ui-button'/>
&nbsp;&nbsp;<input type='button' value='Reset' id='button-Effacer' class='ui-button' onclick='location.href="index.php?page=conges/cet.php&reset=1"' />
</p>
</form>
<table id='tableCET' class='CJDataTable' data-sort='[[1]]'>
<thead>
<tr><th class='dataTableNoSort' >&nbsp;</th>
EOD;
if ($adminN1) {
    echo "<th>Agent</th>";
}
echo <<<EOD
  <th>Jours</th><th>Crédits</th>
  <th class='dataTableDateFR'>Demande</th>
  <th>Validation</th>
  <th class='dataTableDateFR'>Date validation</th></tr>
  </thead>
  <tbody>
EOD;

foreach ($cet as $elem) {
    $saisie_par=null;
    if ($elem['saisie_par'] and $elem['saisie_par']!=$elem['perso_id']) {
        $saisie_par=", ".nom($elem['saisie_par'], 'nom p', $agents);
    }
    $validation="Demand&eacute;e";
    $validationStyle="font-weight:bold;";
    $validationDate=null;
    $credits=null;

    if ($elem['valide_n2']>0) {
        $validation="Accept&eacute;, ".nom($elem['valide_n2'], 'nom p', $agents);
        $validationStyle=null;
        $validationDate=dateFr($elem['validation_n2'], true);
        if ($elem['solde_prec']!=null and $elem['solde_actuel']!=null) {
            $credits="{$elem['solde_prec']} &rarr; {$elem['solde_actuel']}";
        }

    } elseif ($elem['valide_n2']<0) {
        $validation="Refus&eacute;, ".nom(-$elem['valide_n2'], 'nom p', $agents);
        $validationStyle="color:red;font-weight:bold;";
        $validationDate=dateFr($elem['validation_n2'], true);
    } elseif ($elem['valide_n1']!=0) {
        $validation="En attente de validation hierarchique, ".nom($elem['valide_n1'], 'nom p', $agents);
        $validationStyle="font-weight:bold;";
        $validationDate=dateFr($elem['validation_n1'], true);
    }


    echo "<tr>";
    echo "<td><a href='javascript:getCET({$elem['id']});'><span class='pl-icon pl-icon-edit' title='Modifier'></span></a></td>\n";
    if ($adminN1) {
        echo "<td>".nom($elem['perso_id'], 'nom p', $agents)."</td>";
    }
    echo "<td>{$elem['jours']}</td><td>$credits</td><td>".dateFr($elem['saisie'], true)."$saisie_par</td>";
    echo "<td style='$validationStyle'>$validation</td><td>$validationDate</td></tr>\n";
}

$button=$adminN1?"Alimenter un CET":"Alimenter mon CET";
echo <<<EOD
</tbody>
</table>
</div> <!-- liste -->

<div class='noprint'>
<br/><button id='cet-dialog-button' class='ui-button'>$button</button>
</div>

<div id="cet-dialog-form" title="Compte &Eacute;pargne Temps" class='noprint' style='display:none;'>
  <p class="validateTips">Veuillez choisir le nombre de jours à verser sur le Compte &Eacute;pargne Temps.</p>
  <form>
  <input type='hidden' name='id' id='cet-id' />
  <fieldset>
    <table class='tableauFiches'>
EOD;
if ($adminN1) {
    echo <<<EOD
    <tr><td><label for="agent">Agent</label></td>
    <td><select id='cet-agent' name='agent' style='text-align:center;'>
      <option value=''>&nbsp;</option>
EOD;
    foreach ($agents as $elem) {
        $selected=$elem['id']==$perso_id?"selected='selected'":null;
        echo "<option value='{$elem['id']}' $selected >".nom($elem['id'], 'nom p', $agents)."</option>\n";
    }
    echo "</select></td></tr>\n";
}

echo <<<EOD
    <tr><td>Reliquat disponible</td>
    <td><label id='cet-reliquat'></label></td></tr>
EOD;

echo <<<EOD
    <tr><td><label for="jours">Nombre de jours à verser</label></td>
    <td><select id='cet-jours' name='jours' style='text-align:center;'>
      </select></td></tr>
    <tr $displayValidation ><td>Validation</td>
      <td><select id='cet-validation'>
	<option value='0'>&nbsp;</option>
	<option value='1' >Accept&eacute; (En attente de validation hi&eacute;rarchique)</option>
	<option value='-1' >Refus&eacute; (En attente de validation hi&eacute;rarchique)</option>
	<option value='2'  $displayValidationN2 >Accept&eacute;</option>
	<option value='-2'  $displayValidationN2 >Refus&eacute;</option>
      </select></td></tr>
    <tr><td><label for="commentaires">Commentaire</label></td>
      <td><textarea name="commentaires" id="cet-commentaires" ></textarea></td></tr>
    </table>
  </fieldset>
  </form>
</div>
EOD;