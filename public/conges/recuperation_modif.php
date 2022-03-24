<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/recuperation_modif.php
Création : 29 août 2013
Dernière modification : 16 février 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de modifier et valider les demandes de récupérations des samedis (formulaire)
*/

use App\Model\Agent;

include_once "class.conges.php";
include_once "include/horaires.php";

// Initialisation des variables
$entityManager = $GLOBALS['entityManager'];
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

$c=new conges();
$c->recupId=$id;
$c->getRecup();
$recup=$c->elements[0];
$perso_id=$recup['perso_id'];

// Droits d'administration niveau 1 et niveau 2
list($adminN1, $adminN2) = $entityManager
    ->getRepository(Agent::class)
    ->setModule('holiday')
    ->forAgent($perso_id)
    ->getValidationLevelFor($_SESSION['login_id']);

// Prevent non manager to access other agents request.
if (!$adminN1 and !$adminN2 and $perso_id != $_SESSION['login_id']) {
    echo "<h3 style='text-align:center;'>Accès refusé</h3>\n";
    echo "<p style='text-align:center;' >\n";
    echo "<a href='javascript:history.back();'>Retour</a></p>\n";
    include(__DIR__ . '/../include/footer.php');
}


// Initialisation des variables (suite)
$agent=nom($recup['perso_id'], "prenom nom");
$date=$recup['date'];
$date2=$recup['date2'];
$dateAlpha=dateAlpha($date);
$date2Alpha=dateAlpha($date2);
$saisie=dateFr($recup['saisie'], true);

$selectAccept = array(null, null, null, null);
$validation = 'Demandé';
$displayRefus = "none";


if ($recup['valide_n1'] > 0 and $recup['valide']==0) {
    $selectAccept[0] = "selected='selected'";
    $validation = $lang['leave_dropdown_accepted_pending'];
}

if ($recup['valide_n1'] < 0 and $recup['valide']==0) {
    $selectAccept[1] = "selected='selected'";
    $validation = $lang['leave_dropdown_refused_pending'];
    $displayRefus = null;
}

if ($recup['valide'] > 0) {
    $selectAccept[2] = "selected='selected'";
    $validation = $lang['leave_dropdown_accepted'];
}

if ($recup['valide'] < 0) {
    $selectAccept[3] = "selected='selected'";
    $validation = $lang['leave_dropdown_refused'];
    $displayRefus = null;
}


// Affichage
echo <<<EOD
<h3>{$lang['comp_time']}</h3>
<form method='post' action='index.php'>
<input type='hidden' name='page' value='conges/recuperation_valide.php' />
<input type='hidden' name='CSRFToken' value='$CSRFSession' />
<input type='hidden' name='id' value='$id' />
<table class='tableauFiches'>
<tr><td>Agent : </td><td>$agent</td></td></tr>
EOD;
if ($config['Recup-DeuxSamedis']) {
    echo "<tr><td>Date(s) concernée(s) : </td><td>$dateAlpha</td></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td>$date2Alpha</td></td></tr>\n";
} else {
    echo "<tr><td>Date concernée : </td><td>$dateAlpha</td></td></tr>\n";
}
echo "<tr><td>Date de la demande : </td><td>$saisie";
if ($recup['saisie_par'] and $recup['saisie_par']!=$recup['perso_id']) {
    echo " par ".nom($recup['saisie_par']);
}

echo "</td></td></tr>\n";
echo "<tr><td>Heures demandées : </td>";

if ($recup['valide'] <= 0) {
    $heures = gmdate('H:i', floor($recup['heures'] * 3600));
    echo "<td>\n";
    echo "<input id='heures' name='heures' class='comptime-timepicker ui-widget-content ui-corner-all' value='$heures'/>";
    echo "</td></tr>\n";
} else {
    echo "<td>".heure4($recup['heures'])."</td></tr>\n";
}
echo "<tr><td>Commentaires : </td>";
if ($recup['valide'] <= 0) {
    echo "<td><textarea name='commentaires'>{$recup['commentaires']}</textarea></td>\n";
} else {
    echo "<td>".str_replace("\n", "<br/>", $recup['commentaires'])."</td>\n";
}

if (($adminN2 and $recup['valide'] <= 0) or ($adminN1 and $recup['valide']==0)) {
    echo "<tr><td>Validation : </td>\n";
    echo "<td><select name='validation'>\n";
    echo "<option value=''>&nbsp;</option>\n";
    if ($adminN1) {
        echo "<option value='2' {$selectAccept[0]}>{$lang['leave_dropdown_accepted_pending']}</option>\n";
        echo "<option value='-2' {$selectAccept[1]}>{$lang['leave_dropdown_refused_pending']}</option>\n";
    }
    if ($adminN2 and ($recup['valide_n1'] > 0 or $config['Conges-Validation-N2'] == 0)) {
        echo "<option value='1' {$selectAccept[2]}>{$lang['leave_dropdown_accepted']}</option>\n";
        echo "<option value='-1' {$selectAccept[3]}>{$lang['leave_dropdown_refused']}</option>\n";
    }
    echo "</select></td></tr>\n";

    echo "<tr style='display:$displayRefus;' class='refus'><td>Motif du refus : </td>\n";
    echo "<td><textarea name='refus'>{$recup['refus']}</textarea></td></tr>\n";
} else {
    echo <<<EOD
  <tr><td>Validation : </td><td>$validation</td></tr>
  <tr style='display:$displayRefus;' class='refus'><td>Motif du refus : </td>
    <td>{$recup['refus']}</td></tr>
EOD;
}
echo <<<EOD
<tr><td colspan='2' class='td_validation'>
<input type='button' class='ui-button' value='Retour' onclick='location.href="/comp-time";' />
EOD;
if ((($adminN1 or $adminN2) and $recup['valide']<=0) or $recup['valide']==0) {
    echo "<input type='submit' class='ui-button' value='Enregistrer'/>";
}
?>
</td></tr>
</table>
</form>

<script type='text/JavaScript'>
$("select[name=validation]").change(function(){
  if(this.value<0){$(".refus").css("display","");}
  else{$(".refus").css("display","none");}
});
</script>