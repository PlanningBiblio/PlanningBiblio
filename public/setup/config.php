<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/setup/config.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche le formulaire demandant le nom, prénom, adresse email et mot de passe du responsable du planning pour créer son
compte lors de l'installation

Page incluse dans le fichier setup/createdb.php
Formulaire soumis au fichier setup/fin.php
*/

if (!isset($dbname)) {
    echo "Access denied";
    exit;
}

?>
<script type='text/Javascript'>
function checkpassword() {
    if ($('#password').val().length < 6 ) {
        alert("Le mot de passe doit comporter au moins 6 caractères !");
        return false;
    }
    if ($('#password').val() != $('#password2').val()) {
        alert("Les mots de passe ne correspondent pas !");
        return false;
    }
    return true;
}
</script>

<h3>Cr&eacute;ation du compte administrateur</h3>
<p>Veuillez entrer ci-dessous les informations demand&eacute;es<br/>
pour la cr&eacute;ation du compte administrateur (login <b>"admin</b>").<br/>
Ce compte servira &agrave; param&eacute;trer l'application. Il aura tous les droits.<br/>
<form name='form' method='post' action='fin.php' onsubmit='return checkpassword();'>
<input type='hidden' name='CSRFToken' value='<?php echo $_SESSION['oups']['CSRFToken']; ?>' />
<fieldset class='ui-widget-content ui-corner-all'>
<table>
<tr><td>Nom de l'administrateur</td>
<td><input type='text' name='nom' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Prénom</td>
<td><input type='text' name='prenom' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Mot de passe</td>
<td><input type='password' name='password' id='password' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Confirmez le mot de passe</td>
<td><input type='password' name='password2' id='password2' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Email</td>
<td><input type='text' name='email' value='' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td colspan='2' style='text-align:center;padding-top:20px;'>
<input type='reset' name='Anuuler' class='ui-button'/>
&nbsp;&nbsp;&nbsp;<input type='submit' value='Créer' class='ui-button'/>
</td></tr>
</table>
</fieldset>
</form>
