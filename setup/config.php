<?php
/*
Planning Biblio, Version 1.8.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : setup/config.php
Création : mai 2011
Dernière modification : 9 octobre 2014
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
<fieldset class='ui-widget-content ui-corner-all'>
<table>
<tr><td>Nom de l'administrateur</td>
<td><input type='text' name='nom' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Prénom</td>
<td><input type='text' name='prenom' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Mot de passe</td>
<td><input type='password' name='password' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Confirmez le mot de passe</td>
<td><input type='password' name='password2' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Email</td>
<td><input type='text' name='email' value='' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td colspan='2' style='text-align:center;padding-top:20px;'>
<input type='reset' name='Anuuler' class='ui-button'/>
&nbsp;&nbsp;&nbsp;<input type='submit' value='Créer' class='ui-button'/>
</td></tr>
</table>
</fieldset>
</form>