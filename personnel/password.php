<?php
/*
Planning Biblio, Version 1.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : personnel/password.php
Création : mai 2011
Dernière modification : 23 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Cette page permet le changement de mot de passe. Formulaire, vérification et validation

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

echo "<h3>Modification du mot de passe</h3>\n";

echo "<h4>".$_SESSION['login_prenom']." ".$_SESSION['login_nom']."</h4>";

if(!isset($_GET['nouveau'])){
  echo "<form name='form' method='get' action='#'>";
  echo "<input type='hidden' name='page' value='personnel/password.php' />\n";
  echo "<table><tr><td>";
  echo "Ancien mot de passe : ";
  echo "</td><td>\n";
  echo "<input type='password' name='ancien'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Nouveau mot de passe : ";
  echo "</td><td>\n";
  echo "<input type='password' name='nouveau'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Confirmer le nouveau mot de passe : ";
  echo "</td><td>\n";
  echo "<input type='password' name='confirm'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "<br/><input type='button' value='Annuler' onclick='history.back();'/>";
  echo "</td><td>\n";
  echo "<br/><input type='submit' value='Modifier'/>\n";
  echo "</td></tr>\n";
  echo "</table></form>\n";
}
else{
  $db=new db();
  $db->query("select login,password,mail from {$dbprefix}personnel where id=".$_SESSION['login_id'].";");
  $login=$db->result[0]['login'];
  $mail=$db->result[0]['mail'];
  if($db->result[0]['password']!=md5($_GET['ancien'])){
    echo "Ancien mot de passe incorrect";
    echo "<br/><br/>\n";
    echo "<a href='javascript:history.back();'>Retour</a>\n";
  }
  elseif($_GET['nouveau']!=$_GET['confirm']){
    echo "Les nouveaux mots de passes ne correspondent pas";
    echo "<br/><br/>\n";
    echo "<a href='javascript:history.back();'>Retour</a>\n";
  }
  else{
    $mdp=$_GET['nouveau'];
    $mdp_crypt=md5($_GET['nouveau']);
    $db=new db();
    $db->query("update {$dbprefix}personnel set password='".$mdp_crypt."' where id=".$_SESSION['login_id'].";");
    echo "Le mot de passe a été changé";
    echo "<br/><br/>\n";
    echo "<a href='javascript:history.go(-2);'>Retour</a>\n";
    sendmail("Modification du mot de passe","Login : $login <br/>Mot de passe : $mdp","$mail");
  }
}
?>