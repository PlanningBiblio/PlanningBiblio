<?php
/**
Planning Biblio, Plugin Congés Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/recuperation_modif.php
Création : 29 août 2013
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de modifier et valider les demandes de récupérations des samedis (formulaire)
*/

include_once "class.conges.php";
include_once "include/horaires.php";

// Initialisation des variables
$admin=in_array(2, $droits)?true:false;
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

$c=new conges();
$c->recupId=$id;
$c->getRecup();
$recup=$c->elements[0];
$perso_id=$recup['perso_id'];

// Sécurité
if (!$admin and $perso_id!=$_SESSION['login_id']) {
    echo "<h3>Récupérations des samedis</h3>\n";
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
    include "include/footer.php";
    exit;
}

// Initialisation des variables (suite)
$agent=nom($recup['perso_id'], "prenom nom");
$date=$recup['date'];
$date2=$recup['date2'];
$dateAlpha=dateAlpha($date);
$date2Alpha=dateAlpha($date2);
$saisie=dateFr($recup['saisie'], true);
$valide=$recup['valide']>0?true:false;
$select5=$recup['valide']>0?"selected='selected'":null;
$select6=$recup['valide']<0?"selected='selected'":null;
$validation=$select5?"Accepté":($select6?"Refusé":"En attente");
$displayRefus=$recup['valide']<0?"":"none";

// Affichage
echo <<<EOD
<h3>Récupérations des samedis</h3>
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

if (!$valide) {
    echo "<td><select id='heures' name='heures' style='font-weight:bold;' >\n";
    echo "<option value=''>&nbsp;</option>\n";
    for ($i=0;$i<17;$i++) {
        $select1=$recup['heures']=="{$i}.00"?"selected='selected'":null;
        $select2=$recup['heures']=="{$i}.25"?"selected='selected'":null;
        $select3=$recup['heures']=="{$i}.50"?"selected='selected'":null;
        $select4=$recup['heures']=="{$i}.75"?"selected='selected'":null;
        echo "<option value='{$i}.00' $select1>{$i}h00</option>\n";
        echo "<option value='{$i}.25' $select2>{$i}h15</option>\n";
        echo "<option value='{$i}.50' $select3>{$i}h30</option>\n";
        echo "<option value='{$i}.75' $select4>{$i}h45</option>\n";
    }
    echo "</select></td></tr>\n";
} else {
    echo "<td>".heure4($recup['heures'])."</td></tr>\n";
}
echo "<tr><td>Commentaires : </td>";
if (!$valide) {
    echo "<td><textarea name='commentaires'>{$recup['commentaires']}</textarea></td>\n";
} else {
    echo "<td>".str_replace("\n", "<br/>", $recup['commentaires'])."</td>\n";
}

if (!$valide and $admin) {
    echo <<<EOD
  <tr><td>Validation : </td>
    <td><select name='validation'>
      <option value=''>&nbsp;</option>
      <option value='{$_SESSION['login_id']}' $select5>Accepté</option>
      <option value='-{$_SESSION['login_id']}' $select6>Refusé</option>
     </select></td></tr>
  <tr style='display:$displayRefus;' class='refus'><td>Motif du refus : </td>
    <td><textarea name='refus'>{$recup['refus']}</textarea></td></tr>
EOD;
} else {
    echo <<<EOD
  <tr><td>Validation : </td><td>$validation</td></tr>
  <tr style='display:$displayRefus;' class='refus'><td>Motif du refus : </td>
    <td>{$recup['refus']}</td></tr>
EOD;
}
echo <<<EOD
<tr><td colspan='2' class='td_validation'>
<input type='button' class='ui-button' value='Retour' onclick='location.href="index.php?page=conges/recuperations.php";' />
EOD;
if (($admin and !$valide) or $recup['valide']==0) {
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