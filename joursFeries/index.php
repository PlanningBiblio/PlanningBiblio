<?php
/*
Planning Biblio, Version 1.9.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : joursFeries/index.php
Création : 25 juillet 2013
Dernière modification : 19 mai 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Pages permettant la gestion des jours fériés et de fermeture.
*/

include "class.joursFeries.php";

// Initalisation des variables
$annee_courante=date("n")<9?(date("Y")-1)."-".(date("Y")):(date("Y"))."-".(date("Y")+1);
$annee_suivante=date("n")<9?(date("Y"))."-".(date("Y")+1):(date("Y")+1)."-".(date("Y")+2);
$annee_select=isset($_GET['annee'])?$_GET['annee']:(isset($_SESSION['oups']['anneeFeries'])?$_SESSION['oups']['anneeFeries']:$annee_courante);
$_SESSION['oups']['anneeFeries']=$annee_select;

$j=new joursFeries();
$j->fetchYears();
$annees=$j->elements;

if(!in_array($annee_suivante,$annees)){
  $annees[]=$annee_suivante;
}
if(!in_array($annee_courante,$annees)){
  $annees[]=$annee_courante;
}

sort($annees);

// Recherche des jours fériés enregistrés dans la base de données et avec la fonction jour_ferie
$j=new joursFeries();
$j->annee=$annee_select;
$j->auto=false;;
$j->fetch();
$jours=$j->elements;

// Notifications
if(isset($_GET['message'])){
  switch($_GET['message']){
    case "OK" : $message="La liste des jours fériés a été modifée avec succès."; $type="highlight"; break;
    case "Erreur" : $message="Une erreur est survenue lors de la modification de la liste des jours fériés."; $type="error"; break;
  }
  if($message){
    echo "<script type='text/JavaScript'>CJInfo('$message','$type');</script>\n";
  }
}

// Affichage
echo <<<EOD
  <div id='joursFeries'>
  <h3>Jours fériés et jours de fermeture</h3>
  <form name='form' method='post' action='index.php'>
  <input type='hidden' name='page' value='joursFeries/valid.php' />

  <!-- Choix de l'année -->
  Sélectionnez l'année à paramétrer 
  <select name='annee' onchange='document.location.href="index.php?page=joursFeries/index.php&annee="+document.form.annee.value;'>
    <option value=''>&nbsp;</option>
EOD;
foreach($annees as $elem){
  $selected=$elem==$annee_select?"selected='selected'":null;
  echo "<option value='$elem' $selected >$elem</option>\n";
}
echo <<<EOD
  </select>

  <!-- Tableau des jours fériés -->
  <table cellspacing='0'>
  <tr class='th'><td>&nbsp;</td><td>Jour</td><td>Férié</td><td>Fermeture</td><td>Nom</td><td>Commentaire</td></tr>
EOD;
$i=0;
// Affichage des jours fériés enregistrés
foreach($jours as $elem){
  $ferie=$elem['ferie']?"checked='checked'":null;
  $fermeture=$elem['fermeture']?"checked='checked'":null;
  $date=dateFr($elem['jour']);
  echo <<<EOD
    <tr id='tr$i'><td><a href='javascript:supprime_jourFerie($i);'>
      <span class='pl-icon pl-icon-drop' title='Supprimer'></span></a></td>
    <td><input type='text' name='jour[$i]' value='$date' class='c100 datepicker' id='jour$i'/></td>
    <td><input type='checkbox' name='ferie[$i]' value='1' $ferie /></td>
    <td><input type='checkbox' name='fermeture[$i]' value='1' $fermeture/></td>
    <td><input type='text' name='nom[$i]' value='{$elem['nom']}'  class='c350'/></td>
    <td><input type='text' name='commentaire[$i]' value='{$elem['commentaire']}'  class='c350'/></td>
EOD;
  $i++;
}
// Affichage de 15 lignes supplémentaires pour l'ajout de nouveaux jours de fermeture
for($j=$i;$j<$i+15;$j++){
  echo <<<EOD
    <tr id='tr$j'><td><a href='javascript:supprime_jourFerie($j);'>
      <span class='pl-icon pl-icon-drop' title='Supprimer'></span></a></td>
    <td><input type='text' name='jour[$j]' class='c100 datepicker' id='jour$j'/></td>
    <td><input type='checkbox' name='ferie[$j]' value='1' /></td>
    <td><input type='checkbox' name='fermeture[$j]' value='1' /></td>
    <td><input type='text' name='nom[$j]' class='c350'/></td>
    <td><input type='text' name='commentaire[$j]' class='c350'/></td>
EOD;

}

echo <<<EOD
  <tr><td colspan='6' style='padding:20px 0 0 20px;'><input type='submit' value='Valider' class='ui-button' /></td></tr>
  </table>
  </form>
EOD;

if(in_array("conges",$plugins)){
  echo "<p>Les jours de fermeture ne seront pas décomptés des congés.</p>\n";
}




?>
</div> <!-- joursFeries -->