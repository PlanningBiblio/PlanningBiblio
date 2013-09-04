<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.4													*
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : setup/config.php													*
* Création : mai 2011														*
* Dernière modification : 14 décembre 2012											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Affiche le formulaire demandant le nom, prénom, adresse email et mot de passe du responsable du planning pour créer son 	*
* compte lors de l'installation													*
*																*
* Page incluse dans le fichier setup/createconfig.php										*
* Formulaire soumis au fichier setup/fin.php											*
*********************************************************************************************************************************/

$tmp="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$tab=explode("setup",$tmp);
$url=$tab[0];
?>
<h3>Configuration</h3>
<form name='form' method='post' action='fin.php'>
<fieldset>
<table>
<tr><td>URL</td>
<?php echo "<td><input type='text' name='url' value='$url' /></td></tr>\n"; ?>
<?php echo "<td><input type='hidden' name='dbprefix' value='{$_POST['dbprefix']}' /></td></tr>\n"; ?>
<tr><td>Prénom du reponsable du planning</td>
<td><input type='text' name='prenom' onchange='createlogin();' /></td></tr>
<tr><td>Nom</td>
<td><input type='text' name='nom'  onchange='createlogin();' /></td></tr>
<tr><td>Login </td>
<td><input type='text' name='login' value='admin'/></td></tr>
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