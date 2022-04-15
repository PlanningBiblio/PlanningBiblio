<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : personnel/password.php
Création : mai 2011
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Cette page permet le changement de mot de passe. Formulaire, vérification et validation

Cette page est appelée par le fichier index.php
*/

$ancien=filter_input(INPUT_GET, "ancien", FILTER_UNSAFE_RAW);
$confirm=filter_input(INPUT_GET, "confirm", FILTER_UNSAFE_RAW);
$nouveau=filter_input(INPUT_GET, "nouveau", FILTER_UNSAFE_RAW);

require_once "class.personnel.php";

echo "<h3>Modification du mot de passe</h3>\n";

echo "<h4>".$_SESSION['login_prenom']." ".$_SESSION['login_nom']."</h4>";

if (!$nouveau) {
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
    echo "<br/><input type='button' value='Annuler' onclick='location.href=\"{$config['URL']}\/agent\";' class='ui-button ui-button-type2'/>";
    echo "<input type='submit' value='Modifier' class='ui-button' style='margin-left:30px;'/>\n";
    echo "</td></tr>\n";
    echo "</table></form>\n";
} else {
    $db=new db();
    $db->query("select login,password,mail from {$dbprefix}personnel where id=".$_SESSION['login_id'].";");
    $login=$db->result[0]['login'];
    $mail=$db->result[0]['mail'];
    if (!empty($config['demo'])) {
        echo "Vous utilisez une version de démonstration.<br/>";
        echo "Votre mot de passe ne peut pas être modifié.<br/>";
        echo "<br/>\n";
        echo "<a href='javascript:history.back();'>Retour</a>\n";
    } elseif (!password_verify($ancien, $db->result[0]['password'])) {
        echo "Ancien mot de passe incorrect";
        echo "<br/><br/>\n";
        echo "<a href='javascript:history.back();'>Retour</a>\n";
    } elseif ($nouveau!=$confirm) {
        echo "Les nouveaux mots de passes ne correspondent pas";
        echo "<br/><br/>\n";
        echo "<a href='javascript:history.back();'>Retour</a>\n";
    } else {
        $mdp=$nouveau;
        $mdp_crypt = password_hash($mdp, PASSWORD_BCRYPT);
        $db=new db();
        $db->query("update {$dbprefix}personnel set password='".$mdp_crypt."' where id=".$_SESSION['login_id'].";");
        echo "Le mot de passe a été changé";
        echo "<br/><br/>\n";
        echo "<a href='index.php'>Retour au planning</a>\n";

        $message="Votre mot de passe Planning Biblio a &eacute;t&eacute; modifi&eacute;";
        $message.="<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";
    
        // Envoi du mail
        $m=new CJMail();
        $m->subject="Modification du mot de passe";
        $m->message=$message;
        $m->to=$mail;
        $m->send();

        // Si erreur d'envoi de mail, affichage de l'erreur
        if ($m->error_CJInfo) {
            echo "<script type='text/javascript'>CJInfo(\"{$m->error_CJInfo}\",\"error\");</script>\n";
        }
    }
}
