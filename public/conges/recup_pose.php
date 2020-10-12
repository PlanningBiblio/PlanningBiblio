<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/recup_pose.php
Création : 12 janvier 2018
Dernière modification : 30 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de poser des récupérations
Accessible par le menu congés / Poser des récupérations.
Si l'option Conges-Recuperations est à 1 (Dissocier, gestion différente des congés et des récupérations)
Inclus dans le fichier index.php
*/

require_once "class.conges.php";

use App\Model\Agent;

if ($config['Conges-Recuperations'] == 0) {
    include __DIR__.'/../include/accessDenied.php';
}

// Initialisation des variables
$CSRFToken = filter_input(INPUT_GET, 'CSRFToken', FILTER_SANITIZE_STRING);
$perso_id = filter_input(INPUT_GET, 'perso_id', FILTER_SANITIZE_NUMBER_INT);
$debut = filter_input(INPUT_GET, 'debut', FILTER_SANITIZE_STRING);
$fin = filter_input(INPUT_GET, 'fin', FILTER_SANITIZE_STRING);

if (!$perso_id) {
    $perso_id = $_SESSION['login_id'];
}
if (!$fin) {
    $fin = $debut;
}

// Gestion des droits d'administration
// NOTE : Ici, pas de différenciation entre les droits niveau 1 et niveau 2
// NOTE : Les agents ayant les droits niveau 1 ou niveau 2 sont admin ($admin, droits 40x et 60x)
// TODO : différencier les niveau 1 et 2 si demandé par les utilisateurs du plugin

$admin = false;
$adminN2 = false;
for ($i = 1; $i <= $config['Multisites-nombre']; $i++) {
    if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
        $admin = true;
    }
    if (in_array((600+$i), $droits)) {
        $adminN2 = true;
    }
}

// Si pas de droits de gestion des congés, on force $perso_id = son propre ID
if (!$admin) {
    $perso_id=$_SESSION['login_id'];
}

// Calcul des crédits de récupération disponibles lors de l'ouverture du formulaire (date du jour)
$c = new conges();
$balance = $c->calculCreditRecup($perso_id);

echo <<<EOD
<h3>Poser des récupérations</h3>
<table border='0'>
<tr style='vertical-align:top'>
<td style='width:700px;'>
EOD;

if (isset($_GET['confirm'])) {	// Confirmation
    // Initialisation des variables
    $debutSQL=dateSQL($debut);
    $finSQL=dateSQL($fin);
    $hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
    $hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
    $commentaires=htmlentities($_GET['commentaires'], ENT_QUOTES|ENT_IGNORE, "UTF-8", false);

    // Enregistrement du congés
    $c=new conges();
    $c->CSRFToken = $CSRFToken;
    $c->add($_GET);
    $id=$c->id;

    // Récupération des adresses e-mails de l'agent et des responsables pour l'envoi des alertes
    $agent = $entityManager->find(Agent::class, $perso_id);
    $nom = $agent->nom();
    $prenom = $agent->prenom();

    // Choix des destinataires en fonction de la configuration
    if ($config['Absences-notifications-agent-par-agent']) {
        $a = new absences();
        $a->getRecipients2(null, $perso_id, 1);
        $destinataires = $a->recipients;
    } else {
        $c = new conges();
        $c->getResponsables($debutSQL, $finSQL, $perso_id);
        $responsables = $c->responsables;

        $a = new absences();
        $a->getRecipients('-A1', $responsables, $agent);
        $destinataires = $a->recipients;
    }

    // Message qui sera envoyé par email
    $message="Nouveau congés: <br/>$prenom $nom<br/>Début : $debut";
    if ($hre_debut!="00:00:00") {
        $message.=" ".heure3($hre_debut);
    }
    $message.="<br/>Fin : $fin";
    if ($hre_fin!="23:59:59") {
        $message.=" ".heure3($hre_fin);
    }
    if ($commentaires) {
        $message.="<br/><br/>Commentaire :<br/>$commentaires<br/>";
    }

    // ajout d'un lien permettant de rebondir sur la demande
    $url = $config['URL'] . '/holiday/edit/' . $id;
    $message.="<br/><br/>Lien vers la demande de cong&eacute; :<br/><a href='$url'>$url</a><br/><br/>";

    // Envoi du mail
    $m=new CJMail();
    $m->subject="Nouveau congés";
    $m->message=$message;
    $m->to=$destinataires;
    $m->send();

    // Si erreur d'envoi de mail, affichage de l'erreur
    $msg2=null;
    $msg2Type=null;
    if ($m->error) {
        $msg2=urlencode($m->error_CJInfo);
        $msg2Type="error";
    }

    $msg=urlencode("La demande de congé a été enregistrée");
    echo "<script type='text/JavaScript'>document.location.href='{$config['URL']}/holiday/index?recup=1&msg=$msg&msgType=success&msg2=$msg2&msg2Type=$msg2Type';</script>\n";
}

// Formulaire
else {
    // Initialisation des variables
    $perso_id=$perso_id?$perso_id:$_SESSION['login_id'];
    $p=new personnel();
    $p->fetchById($perso_id);
    $nom=$p->elements[0]['nom'];
    $prenom=$p->elements[0]['prenom'];
    $credit = number_format((float) $p->elements[0]['conges_credit'], 2, '.', ' ');
    $reliquat = number_format((float) $p->elements[0]['conges_reliquat'], 2, '.', ' ');
    $anticipation = number_format((float) $p->elements[0]['conges_anticipation'], 2, '.', ' ');
    $credit2 = heure4($credit);
    $reliquat2 = heure4($reliquat);
    $anticipation2 = heure4($anticipation);
    $recuperation = number_format((float) $balance[1], 2, '.', ' ');
    $recuperation2=heure4($recuperation);

    // Affichage du formulaire
    echo "<form name='form' action='index.php' method='get' id='form'>\n";
    echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
    echo "<input type='hidden' name='page' value='conges/recup_pose.php' />\n";
    echo "<input type='hidden' name='confirm' value='confirm' />\n";
    echo "<input type='hidden' name='reliquat' value='$reliquat' />\n";
    echo "<input type='hidden' name='recuperation' id='recuperation' value='$recuperation' />\n";
    echo "<input type='hidden' name='recuperation_prev' id='recuperation_prev' value='{$balance[4]}' />\n";
    echo "<input type='hidden' name='credit' value='$credit' />\n";
    echo "<input type='hidden' name='anticipation' value='$anticipation' />\n";
    echo "<input type='hidden' id='agent' value='{$_SESSION['login_nom']} {$_SESSION['login_prenom']}' />\n";
    echo "<input type='hidden' id='conges-recup' value='1' />\n";
    echo "<input type='hidden' id='is-recover' value='1' />\n";
    echo "<table border='0'>\n";
    echo "<tr><td style='width:350px;'>\n";
    echo "Nom, prénom : \n";
    echo "</td><td style='width:250px;'>\n";

    if ($admin) {
        // Si l'option "Absences-notifications-agent-par-agent" est cochée, filtrer les agents à afficher dans le menu déroulant pour permettre la sélection des seuls agents gérés
        if ($config['Absences-notifications-agent-par-agent'] and !$adminN2) {
            $perso_ids = array($_SESSION['login_id']);

            $db = new db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $perso_ids[] = $elem['perso_id'];
                }
            }

            $perso_ids = implode(',', $perso_ids);

            $db_perso=new db();
            $db_perso->select2('personnel', null, array('supprime' => '0', 'id' => "IN$perso_ids"), 'ORDER BY nom,prenom');
        }

        // Si l'option "Absences-notifications-agent-par-agent" n'est pas cochée, on affiche tous les agents dans le menu déroulant
        else {
            $db_perso=new db();
            $db_perso->select2('personnel', null, array('supprime' => '0'), 'ORDER BY nom,prenom');
        }

        echo "<select name='perso_id' id='perso_id' onchange='document.location.href=\"index.php?page=conges/recup_pose.php&perso_id=\"+this.value;' style='width:98%;'>\n";
        foreach ($db_perso->result as $elem) {
            if ($perso_id==$elem['id']) {
                echo "<option value='".$elem['id']."' selected='selected'>".$elem['nom']." ".$elem['prenom']."</option>\n";
            } else {
                echo "<option value='".$elem['id']."'>".$elem['nom']." ".$elem['prenom']."</option>\n";
            }
        }
        echo "</select>\n";
    } else {
        echo "<input type='hidden' name='perso_id' id='perso_id' value='{$_SESSION['login_id']}' />\n";
        echo $_SESSION['login_nom']." ".$_SESSION['login_prenom'];
    }
    echo "</td></tr>\n";
    echo "<tr><td style='padding-top:15px;'>\n";
    echo "Journée(s) entière(s) : \n";
    echo "</td><td style='padding-top:15px;'>\n";
    echo "<input type='checkbox' name='allday' onclick='all_day();'/>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "Date de début : \n";
    echo "</td><td>";
    echo "<input type='text' name='debut' id='debut' value='$debut' class='datepicker googleCalendarTrigger checkdate' style='width:97%;'/>&nbsp;\n";
    echo "</td></tr>\n";
    echo "<tr id='hre_debut'><td>\n";
    echo "Heure de début : \n";
    echo "</td><td>\n";
    echo "<select name='hre_debut' id='hre_debut_select' style='width:98%;' class='googleCalendarTrigger checkdate'>\n";
    selectHeure(7, 23, true);
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "Date de fin : \n";
    echo "</td><td>";
    echo "<input type='text' name='fin' id='fin' value='$fin'  class='datepicker googleCalendarTrigger checkdate' style='width:97%;'/>&nbsp;\n";
    echo "</td></tr>\n";
    echo "<tr id='hre_fin'><td>\n";
    echo "Heure de fin : \n";
    echo "</td><td>\n";
    echo "<select name='hre_fin' id='hre_fin_select' style='width:98%;' class='googleCalendarTrigger checkdate' onfocus='setEndHour();'>\n";
    selectHeure(7, 23, true);
    echo "</select>\n";
    echo "</td></tr>\n";
  
    echo <<<EOD
    <tr><td style='padding-top:15px;'>Nombre d'heures : </td>
      <td style='padding-top:15px;'>
      <div id='nbHeures' style='padding:0 5px; width:50px;'></div>
      <input type='hidden' name='heures' value='0' />
      <input type='hidden' name='minutes' value='0' />
      <input type='hidden' id='erreurCalcul' value='false' />
      </td></tr>

  <tr><td>Nombre de jours (7h/jour) : </td>
    <td>
      <div id='nbJours' style='padding:0 5px; width:50px;'></div>
    </td></tr>

  <tr><td colspan='2' style='padding-top:20px;'>
EOD;

    echo "Ces heures seront débitées sur les crédits de récupérations.";
    echo "<input type='hidden' name='debit' value='recuperation' />\n";
    echo "</td></tr>\n";

    echo "<tr><td colspan='2'>\n";
    echo "<table border='0'>\n";

    echo "<tr class='balance_tr'><td style='width:348px;'>Solde disponible au <span class='balance_date'>".dateFr($balance[0])."</span> : </td>\n";
    echo "<td id='balance_before'>".heure4($balance[1])."</td>\n";
    echo "<td>(après débit : <span id='recup4'>".heure4($balance[1])."</span>)</td></tr>\n";

    echo "<tr class='balance_tr'><td>Solde prévisionnel<sup>*</sup> au <span class='balance_date'>".dateFr($balance[0])."</span> : </td>\n";
    echo "<td id='balance2_before'>".heure4($balance[4])."</td>\n";
    echo "<td>(après débit : <span id='balance2_after'>".heure4($balance[4])."</span>)</td></tr>\n";

    echo "</table>\n";
    echo "</td></tr>\n";

    echo "<tr valign='top'><td style='padding-top:15px;'>\n";
    echo "Commentaires : \n";
    echo "</td><td style='padding-top:15px;'>\n";
    echo "<textarea name='commentaires' cols='16' rows='5' style='width:97%;'></textarea>\n";
    echo "</td></tr><tr><td>&nbsp;\n";
    echo "</td></tr><tr><td colspan='2' style='text-align:center;'>\n";
    echo "<input type='button' value='Annuler' onclick='document.location.href=\"{$config['URL']}/holiday/index?recup=1\";' class='ui-button'/>";
    echo "&nbsp;&nbsp;\n";
    echo "<input type='button' value='Valider' class='ui-button' onclick='verifConges();' style='margin-left:20px;' id='submit-button'/>\n";
    echo "<div id='google-calendar-div' class='inline'></div>\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan='2' style='padding-top:30px; font-style:italic;'><sup>*</sup> Le solde prévisionnel tient compte des demandes des récupérations non validées (crédits et utilisations).</td></tr>\n";
    echo "</table>\n";
    echo "</form>\n";
}

echo "</td><td style='color:#FF5E0E;'>\n";

$date=date("Y-m-d");
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
if ($db->result) {
    echo "<b>Informations sur les congés :</b><br/><br/>\n";
    foreach ($db->result as $elem) {
        echo "Du ".dateFr($elem['debut'])." au ".dateFr($elem['fin'])." :<br/>".str_replace("\n", "<br/>", $elem['texte'])."<br/><br/>\n";
    }
}
?>
</td></tr></table>
