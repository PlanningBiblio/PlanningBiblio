<?php
/*
Planning Biblio, Version 1.5.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : setup/config.php
Création : mai 2011
Dernière modification : 6 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le formulaire demandant le nom, prénom, adresse email et mot de passe du responsable du planning pour créer son 
compte lors de l'installation

Page incluse dans le fichier setup/createconfig.php
Formulaire soumis au fichier setup/fin.php
*/
?>
<h3>Cr&eacute;ation du compte administrateur</h3>
<p>Veuillez entrer ci-dessous les informations demand&eacute;es<br/>
pour la cr&eacute;ation du compte administrateur (login <b>"admin</b>").<br/>
Ce compte servira &agrave; param&eacute;trer l'application. Il aura tous les droits.<br/>
<form name='form' method='post' action='fin.php'>
<input type='hidden' name='dbprefix' value='<?php echo $_POST['dbprefix']; ?>' />
<fieldset>
<table>
<tr><td>Nom de l'administrateur</td>
<td><input type='text' name='nom' /></td></tr>
<tr><td>Prénom</td>
<td><input type='text' name='prenom' /></td></tr>
<tr><td>Mot de passe</td>
<td><input type='password' name='password' /></td></tr>
<tr><td>Confirmez le mot de passe</td>
<td><input type='password' name='password2' /></td></tr>
<tr><td>Email</td>
<td><input type='text' name='email' value='' /></td></tr>
<tr><td colspan='2' style='text-align:center'>
<input type='reset' name='Anuuler'/>
&nbsp;&nbsp;&nbsp;<input type='submit' value='Créer' />
</td></tr>
</table>
</fieldset>
</form>