<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/setup/index.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Première page d'installation. Affiche le formulaire demandant les informations de connexion au serveur MySQL, le nom de la
base de données et de l'utilisateur MySQL à créer.

Formulaire soumis au fichier setup/createdb.php
*/

session_start();
session_destroy();
session_start();


$password = substr(bin2hex(random_bytes(32)), 0, 16);

// Génération d'un CSRF Token
if (empty($_SESSION['oups']['CSRFToken'])) {
    $_SESSION['oups']['CSRFToken'] = bin2hex(random_bytes(32));
}

include "header.php";

if (file_exists(__DIR__ . '/../../.env.local')) {
    echo "<p style='color:red;'>L'installation a déjà été effectuée !</p>\n";
    echo "<p>Si vous souhaitez recommencer l'installation, supprimez, déplacez ou renommez le fichier .env.local</p>\n";
    include "footer.php";
    exit;
}

?>
<h3>Création de la base de donnée</h3>
<p>
Veuillez entrer ci-dessous les informations<br/>
n&eacute;cessaires &agrave; la cr&eacute;ation de la base de donn&eacute;es.
</p>
<form name='form' method='post' action='createconfig.php'>
<input type='hidden' name='dbport' value='3306' />
<fieldset class='ui-widget-content ui-corner-all' >
<table>
<tr><td>Nom d'h&ocirc;te ou adresse IP du serveur MySQL</td>
<td><input type='text' name='dbhost' value='localhost' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Login administrateur du serveur MySQL</td>
<td><input type='text' name='adminuser' value='root' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Mot de passe administrateur</td>
<td><input type='password' name='adminpass' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Nom de la base de donnée à utiliser<br/>(sera cr&eacute;&eacute;e si n&apos;existe pas)</td>
<td><input type='text' name='dbname' value='planningBiblio' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Préfix des tables</td>
<td><input type='text' name='dbprefix' value='' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Nom d'utilisateur</td>
<td><input type='text' name='dbuser' value='planningBiblio' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Mot de passe</td>
<td><input type='text' name='dbpass' value='<?php echo $password; ?>' class='ui-widget-content ui-corner-all' /></td></tr>
<tr><td>Supprimer la base si elle existe ?</td>
<td><input type='checkbox' name='dropdb' /></td></tr>
<tr><td>Supprimer l'utilisateur s'il existe ?</td>
<td><input type='checkbox' name='dropuser' /></td></tr>
<tr><td colspan='2' style='text-align:center;padding-top:20px;'>
<input type='reset' name='Anuuler' class='ui-button'/>
&nbsp;&nbsp;&nbsp;<input type='submit' value='Créer' class='ui-button'/>
</td></tr>
</table>
</fieldset>
</form>
<?php
include "footer.php";
?>