<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : personnel/password.php
Création : mai 2011
Dernière modification : 13 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Cette page permet le changement de mot de passe. Formulaire, vérification et validation

Cette page est appelée par le fichier index.php
*/

$ancien=filter_input(INPUT_GET,"ancien",FILTER_UNSAFE_RAW);
$confirm=filter_input(INPUT_GET,"confirm",FILTER_UNSAFE_RAW);
$nouveau=filter_input(INPUT_GET,"nouveau",FILTER_UNSAFE_RAW);

require_once "class.personnel.php";

echo "<h3>Modification du mot de passe</h3>\n";

echo "<h4>".$_SESSION['login_prenom']." ".$_SESSION['login_nom']."</h4>";

if(!$nouveau){
  echo "<form name='form' method='get' action='#'>";
  echo "<input type='hidden' name='page' value='personnel/password.php' />\n";
  echo "<table class='tableauFiches'><tr><td>";
  echo "Ancien mot de passe : ";
  echo "</td><td>\n";
  echo "<input type='password' name='ancien' class='ui-widget-content ui-corner-all'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Nouveau mot de passe : ";
  echo "</td><td>\n";
  echo "<input type='password' name='nouveau' class='ui-widget-content ui-corner-all'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td>\n";
  echo "Confirmer le nouveau mot de passe : ";
  echo "</td><td>\n";
  echo "<input type='password' name='confirm' class='ui-widget-content ui-corner-all'/>\n";
  echo "</td></tr>\n";
  echo "<tr><td colspan='2' style='text-align:center;'>\n";
  echo "<br/><input type='button' value='Annuler' onclick='history.back();' class='ui-button'/>";
  echo "<input type='submit' value='Modifier' class='ui-button' style='margin-left:30px;'/>\n";
  echo "</td></tr>\n";
  echo "</table></form>\n";
}
else{
  $db=new db();
  $db->query("select login,password,mail from {$dbprefix}personnel where id=".$_SESSION['login_id'].";");
  $login=$db->result[0]['login'];
  $mail=$db->result[0]['mail'];
  if($db->result[0]['password']!=md5($ancien)){
    echo "Ancien mot de passe incorrect";
    echo "<br/><br/>\n";
    echo "<a href='javascript:history.back();'>Retour</a>\n";
  }
  elseif($nouveau!=$confirm){
    echo "Les nouveaux mots de passes ne correspondent pas";
    echo "<br/><br/>\n";
    echo "<a href='javascript:history.back();'>Retour</a>\n";
  }
  else{
    $mdp=$nouveau;
    $mdp_crypt=md5($nouveau);
    $db=new db();
    $db->query("update {$dbprefix}personnel set password='".$mdp_crypt."' where id=".$_SESSION['login_id'].";");
    echo "Le mot de passe a été changé";
    echo "<br/><br/>\n";
    echo "<a href='javascript:history.go(-2);'>Retour</a>\n";
    sendmail("Modification du mot de passe","Login : $login <br/>Mot de passe : $mdp","$mail");
  }
}
?>