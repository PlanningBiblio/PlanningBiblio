<?php
/*
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : plugins/planningHebdo/index.php
Création : 25 juillet 2013
Dernière modification : 22 mai 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche la liste des plannings de présence pour l'administrateur
Page accessible à partir du menu administration/planning de présence
*/

require_once "class.planningHebdo.php";

$post=filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);

// Mise à jour de la configHebdo
if($post){
  $error=false;
  $p=new planningHebdo();
  $p->updateConfig($post);
  $error=$p->error?true:$error;

  $p=new planningHebdo();
  $p->updatePeriodes($post);
  $error=$p->error?true:$error;
  $message=null;

  // Notifications
  if($error){
    $message="Une erreur est survenue lors de la modification de la configuration."; $type="error";
  }
  else{
    $message="La configuration a été modifiée avec succés."; $type="highlight";
  }
  if($message){
    echo "<script type='text/JavaScript'>information('$message','$type');</script>\n";
  }
}

// Recherche de la config
$p=new planningHebdo();
$p->getConfig();
$configHebdo=$p->config;

// Initialisation des variables
$annee_courante=date("n")<9?(date("Y")-1)."-".(date("Y")):(date("Y"))."-".(date("Y")+1);
$annee_suivante=date("n")<9?(date("Y"))."-".(date("Y")+1):(date("Y")+1)."-".(date("Y")+2);
$checked[0]=$configHebdo['periodesDefinies']?"checked='checked'":null;
$checked[1]=$configHebdo['periodesDefinies']?null:"checked='checked'";
$select[0]=$configHebdo['notifications']=="droit"?"selected='selected'":null;
$select[1]=$configHebdo['notifications']=="Mail-Planning"?"selected='selected'":null;

// Recherche des dates de début et de fin de chaque période
$p->dates=array($annee_courante,$annee_suivante);
$p->getPeriodes();
$dates=$p->periodesFr;


echo <<<EOD
<h3>Configuration du module "Planning Hebdo"</h3>
<form name='form' action='index.php' method='post'>
<input type='hidden' name='page' value='planningHebdo/configuration.php'/>
<input type='hidden' name='annee[0]' value='$annee_courante'/>
<input type='hidden' name='annee[1]' value='$annee_suivante'/>

<table>
<tr><td colspan='2'>Utiliser des périodes définies pour les plannings hebdomadaires</td>
  <td><input type='radio' value='1' name='periodesDefinies' {$checked[0]} /> Oui
    <input type='radio' value='0' name='periodesDefinies' {$checked[1]} /> Non</td></tr>
<tr><td colspan='3' style='padding:20px 0 20px 0;'>Si vous utilisez les périodes définies, veuillez saisir ci-dessous les dates de début et de fin de chaque période</td></tr>
<tr><td>Année $annee_courante, horaires normaux</td>
  <td>Début <input type='text' name='dates[0][0]' value='{$dates[0][0]}' class='datepicker' />
  <td>Fin <input type='text' name='dates[0][1]' value='{$dates[0][1]}' class='datepicker' />
  </td></tr>
<tr><td>Année $annee_courante, horaires réduits</td>
  <td>Début <input type='text' name='dates[0][2]' value='{$dates[0][2]}' class='datepicker' />
  <td>Fin <input type='text' name='dates[0][3]' value='{$dates[0][3]}' class='datepicker' />
  </td></tr>
<tr><td>Année $annee_suivante, horaires normaux</td>
  <td>Début <input type='text' name='dates[1][0]' value='{$dates[1][0]}' class='datepicker' />
  <td>Fin <input type='text' name='dates[1][1]' value='{$dates[1][1]}' class='datepicker' />
  </td></tr>
<tr><td>Année $annee_suivante, horaires réduits</td>
  <td>Début <input type='text' name='dates[1][2]' value='{$dates[1][2]}' class='datepicker' />
  <td>Fin <input type='text' name='dates[1][3]' value='{$dates[1][3]}' class='datepicker' />
  </td></tr>
<tr><td style='padding:20px 0 20px 0;'>Envoyer les notifications : </td>
  <td colspan='2' style='padding:20px 0 20px 0;'><select name='notifications' style='width:100%;'>
    <option value=''></option>
    <option {$select[0]} value='droit'>Aux agents ayant le droit de gérer les plannings de présences</option>
    <option {$select[1]} value='Mail-Planning'>A la cellule planning</option>
    </select>
  </td></tr>
<tr><td colspan='3' style='padding:20px 0 0 30px;'>
  <input type='button' value='Retour' onclick='document.location.href="index.php?page=planningHebdo/index.php";' class='ui-button' />
  <input type='submit' value='Valider' style='margin-left:30px;' class='ui-button' /></td></tr>
</table>
</form>
EOD;
?>