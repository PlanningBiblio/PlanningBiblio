<?php
/*
Planning Biblio, Version 1.5.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : setup/index.php
Création : mai 2011
Dernière modification : 6 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Première page d'installation. Affiche le formulaire demandant les informations de connexion au serveur MySQL, le nom de la
base de données et de l'utilisateur MySQL à créer.

Formulaire soumis au fichier setup/createdb.php
*/

session_start();
session_destroy();
$version="1.5.7";

include "header.php";
include "../include/function.php";
$password=gen_trivial_password(16);

$Fnm = "../include/config.php";
if(!$inF=fopen($Fnm,"w\n")){
  $msg="<br/>Important : Avant de continuer,<br/> Veuillez donner les droits d'&eacute;criture/modification <br/>aux dossiers \"include\" et \"data\".\n";
  $msg.="<br/><a href='index.php'>Re-vérifier</a>";
}

echo "<h2>Installation de Planning Biblio $version</h2>\n";
?>
<h3>Création de la base de donnée</h3>
<p>
Veuillez entrer ci-dessous les informations<br/>
n&eacute;cessaires &agrave; la cr&eacute;ation de la base de donn&eacute;es.
</p>
<form name='form' method='post' action='createdb.php'>
<fieldset>
<table>
<tr><td>Login administrateur du serveur MySQL</td>
<td><input type='text' name='adminuser' value='root' /></td></tr>
<tr><td>Mot de passe administrateur</td>
<td><input type='password' name='adminpass' /></td></tr>
<tr><td>Nom de la base de donnée à créer</td>
<td><input type='text' name='dbname' value='planningBiblio' /></td></tr>
<tr><td>Préfix des tables</td>
<td><input type='text' name='dbprefix' value='' /></td></tr>
<tr><td>Nom d'utilisateur</td>
<td><input type='text' name='dbuser' value='planningBiblio' /></td></tr>
<tr><td>Mot de passe</td>
<td><input type='text' name='dbpass' value='<?php echo $password; ?>' /></td></tr>
<tr><td>Supprimer la base si elle existe ?</td>
<td><input type='checkbox' name='dropdb' /></td></tr>
<tr><td>Supprimer l'utilisateur s'il existe ?</td>
<td><input type='checkbox' name='dropuser' /></td></tr>
<tr><td colspan='2' style='text-align:center'>
<input type='reset' name='Anuuler'/>
&nbsp;&nbsp;&nbsp;<input type='submit' value='Créer' />
</td></tr>
<tr><td colspan='2' style='text-align:center;color:red;'><?php echo $msg; ?></td></tr>
</table>
</fieldset>
</form>
<?php
include "footer.php";
?>