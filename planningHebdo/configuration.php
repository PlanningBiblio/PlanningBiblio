<?php
/**
Planning Biblio, Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : plugins/planningHebdo/index.php
Création : 25 juillet 2013
Dernière modification : 21 avril 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des plannings de présence pour l'administrateur
Page accessible à partir du menu administration/planning de présence
*/

require_once "class.planningHebdo.php";

$post=filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);

// Mise à jour de la configHebdo
if($post){
  $p=new planningHebdo();
  $p->updatePeriodes($post);
  $error=$p->error?true:false;
  $message=null;

  // Notifications
  if($error){
    $message="Une erreur est survenue lors des p&eacute;riodes."; $type="error";
  }
  else{
    $message="Les p&eacute;riodes ont été modifiées avec succès."; $type="highlight";
  }
  if($message){
    echo "<script type='text/JavaScript'>CJInfo('$message','$type');</script>\n";
  }
}

// Initialisation des variables
$annee_courante=date("n")<9?(date("Y")-1)."-".(date("Y")):(date("Y"))."-".(date("Y")+1);
$annee_suivante=date("n")<9?(date("Y"))."-".(date("Y")+1):(date("Y")+1)."-".(date("Y")+2);

// Recherche des dates de début et de fin de chaque période
$p->dates=array($annee_courante,$annee_suivante);
$p->getPeriodes();
$dates=$p->periodesFr;


echo <<<EOD
<h3>Configuration des p&eacute;riodes</h3>
<form name='form' action='index.php' method='post'>
<input type='hidden' name='page' value='planningHebdo/configuration.php'/>
<input type='hidden' name='annee[0]' value='$annee_courante'/>
<input type='hidden' name='annee[1]' value='$annee_suivante'/>

<table class='tableauFiches'>
<tr><td colspan='5' style='padding:20px 0 20px 0;'>Veuillez saisir ci-dessous les dates de début et de fin de chaque période</td></tr>
<tr><td>Année $annee_courante, horaires normaux</td>
  <td>Début</td>
  <td><input type='text' name='dates[0][0]' value='{$dates[0][0]}' class='datepicker' />
  <td>Fin</td>
  <td><input type='text' name='dates[0][1]' value='{$dates[0][1]}' class='datepicker' />
  </td></tr>
<tr><td>Année $annee_courante, horaires réduits</td>
  <td>Début</td>
  <td><input type='text' name='dates[0][2]' value='{$dates[0][2]}' class='datepicker' />
  <td>Fin</td>
  <td><input type='text' name='dates[0][3]' value='{$dates[0][3]}' class='datepicker' />
  </td></tr>
<tr><td>Année $annee_suivante, horaires normaux</td>
  <td>Début</td>
  <td><input type='text' name='dates[1][0]' value='{$dates[1][0]}' class='datepicker' />
  <td>Fin</td>
  <td><input type='text' name='dates[1][1]' value='{$dates[1][1]}' class='datepicker' />
  </td></tr>
<tr><td>Année $annee_suivante, horaires réduits</td>
  <td>Début</td>
  <td><input type='text' name='dates[1][2]' value='{$dates[1][2]}' class='datepicker' />
  <td>Fin</td>
  <td><input type='text' name='dates[1][3]' value='{$dates[1][3]}' class='datepicker' />
  </td></tr>
<tr><td colspan='5' style='padding:40px 0 0 30px;'>
  <input type='button' value='Retour' onclick='document.location.href="index.php?page=planningHebdo/index.php";' class='ui-button' />
  <input type='submit' value='Valider' style='margin-left:30px;' class='ui-button' /></td></tr>
</table>
</form>
EOD;
?>