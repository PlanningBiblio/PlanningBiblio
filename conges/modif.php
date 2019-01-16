<?php
/**
Planning Biblio, Plugin Congés Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/modif.php
Création : 1er août 2013
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié <etienne.cavalie@unice.fr>

Description :
Fichier permettant voir ou de modifier un congé
Accessible par la page conges/voir.php
Inclus dans le fichier index.php
*/

require_once "class.conges.php";

// Initialisation des variables
$get=filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$commentaires=filter_input(INPUT_GET, "commentaires", FILTER_SANITIZE_STRING);
$confirm=filter_input(INPUT_GET, "confirm", FILTER_CALLBACK, array("options"=>"sanitize_on"));
$CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$debut=filter_input(INPUT_GET, "debut", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_input(INPUT_GET, "fin", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$hre_debut=filter_input(INPUT_GET, "hre_debut", FILTER_CALLBACK, array("options"=>"sanitize_time"));
$hre_fin=filter_input(INPUT_GET, "hre_fin", FILTER_CALLBACK, array("options"=>"sanitize_time_end"));
$menu=filter_input(INPUT_GET, "menu", FILTER_SANITIZE_STRING);
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);
$refus=filter_input(INPUT_GET, "refus", FILTER_SANITIZE_STRING);
$valide=filter_input(INPUT_GET, "valide", FILTER_SANITIZE_NUMBER_INT);

// Elements du congé demandé
$c=new conges();
$c->id=$id;
$c->fetch();
if (!array_key_exists(0, $c->elements)) {
    echo "<h3>Congés</h3>\n";
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
    include "include/footer.php";
}
$data=$c->elements[0];

if (!$perso_id) {
    $perso_id=$data['perso_id'];
}

if (!in_array(2, $droits) and $perso_id!=$_SESSION['login_id']) {
    echo "<h3>Congés</h3>\n";
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
    include "include/footer.php";
    exit;
}

if ($config['Multisites-nombre']>1) {
    $p=new personnel();
    $p->fetchById($perso_id);
    // $droitsConges = droits nécessaires pour adminN2 si multisites

    // TODO : Vérifier le bon fonctionnement de la validation N2 en mono et en multisites.
    // $droitsConges=array(2); ajouté en 10/2017 suite à la découverte du fait que le droit "Gestion des congés, validation N2" n'est pas géré site par site
    //   $droitsConges=array();
    $droitsConges=array(2);
    if (is_array($p->elements[0]['sites'])) {
        foreach ($p->elements[0]['sites'] as $site) {
            $droitsConges[]=400+$site;
        }
    }
} else {
    $droitsConges=array(2);
}
$admin=in_array(7, $droits)?true:false;
$admin=in_array(2, $droits)?true:$admin;
$adminN2=false;
foreach ($droitsConges as $elem) {
    $adminN2=in_array($elem, $droits)?true:$adminN2;
}

if ($confirm) {
    $fin=$fin?$fin:$debut;
    $debutSQL=dateSQL($debut);
    $finSQL=dateSQL($fin);

    // Enregistre la modification du congés
    $c=new conges();
    $c->CSRFToken = $CSRFToken;
    $c->update($get);

    // Envoi d'une notification par email
    // Récupération des adresses e-mails de l'agent et des responsables pour m'envoi des alertes
    $c=new conges();
    $c->getResponsables($debutSQL, $finSQL, $perso_id);
    $responsables=$c->responsables;

    $p=new personnel();
    $p->fetchById($perso_id);
    $nom=$p->elements[0]['nom'];
    $prenom=$p->elements[0]['prenom'];
    $mail=$p->elements[0]['mail'];
    $mailsResponsables=$p->elements[0]['mails_responsables'];

    // Choix du sujet et des destinataires en fonction du degré de validation
    switch ($valide) {
    // Modification sans validation
    case 0:
      $sujet="Modification de congés";
      $notifications=2;
      break;
    // Validations Niveau 2
    case 1:
      $sujet="Validation de congés";
      $notifications=4;
      break;
    case -1:
      $sujet="Refus de congés";
      $notifications=4;
      break;
    // Validations Niveau 1
    case 2:
      $sujet="Congés en attente de validation hiérarchique";
      $notifications=3;
      break;
    case -2:
      $sujet="Congés en attente de validation hiérarchique";
      $notifications=3;
      break;
  }

    // Choix des destinataires en fonction de la configuration
    $a=new absences();
    $a->getRecipients($notifications, $responsables, $mail, $mailsResponsables);
    $destinataires=$a->recipients;

    // Message qui sera envoyé par email
    $message="$sujet : <br/>$prenom $nom<br/>Début : $debut";
    if ($hre_debut!="00:00:00") {
        $message.=" ".heure3($hre_debut);
    }
    $message.="<br/>Fin : $fin";
    if ($hre_fin!="23:59:59") {
        $message.=" ".heure3($hre_fin);
    }
    if ($commentaires) {
        $message.="<br/><br/>Commentaires :<br/>$commentaires<br/>";
    }
    if ($refus and $valide==-1) {
        $message.="<br/>Motif du refus :<br/>$refus<br/>";
    }

    // ajout d'un lien permettant de rebondir sur la demande
    $url=createURL("conges/modif.php&id=$id");
    $message.="<br/><br/>Lien vers la demande de cong&eacute; :<br/><a href='$url'>$url</a><br/><br/>";

    // Envoi du mail
    $m=new CJMail();
    $m->subject=$sujet;
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
    $msg=urlencode("Le congé a été modifié avec succès.");
    echo "<script type='text/JavaScript'>document.location.href=\"index.php?page=conges/voir.php&msg=$msg&msgType=success&msg2=$msg2&msg2Type=$msg2Type\"</script>\n";
} else {	// Formulaire
    $valide=$data['valide']>0?true:false;
    $selectAccept[0]=$data['valide']>0?"selected='selected'":null;
    $selectAccept[1]=$data['valide']<0?"selected='selected'":null;
    $selectAccept[2]=($data['valide_n1']>0 and $data['valide']==0)?"selected='selected'":null;
    $selectAccept[3]=($data['valide_n1']<0 and $data['valide']==0)?"selected='selected'":null;
    $displayRefus=$data['valide']>=0?"display:none;":null;
    $displayRefus=($data['valide_n1']<0 and $admin)?null:$displayRefus;
    $perso_id=$data['perso_id'];
    $debut=dateFr(substr($data['debut'], 0, 10));
    $fin=dateFr(substr($data['fin'], 0, 10));
    $hre_debut=substr($data['debut'], -8);
    $hre_fin=substr($data['fin'], -8);
    $allday=null;
    $displayHeures=null;
    if ($hre_debut=="00:00:00" and $hre_fin=="23:59:59") {
        $allday="checked='checked'";
        $displayHeures="style='display:none;'";
    }
    $jours=number_format(($data['heures']/7), 2, ".", " ");
    $tmp=explode(".", $data['heures']);
    $heures=$tmp[0];
    $minutes=$tmp[1];
    $selectRecup=$data['debit']=="recuperation"?"selected='selected'":null;
    $selectCredit=$data['debit']=="credit"?"selected='selected'":null;

    // Crédits
    $p=new personnel();
    $p->fetchById($perso_id);
    $nom=$p->elements[0]['nom'];
    $prenom=$p->elements[0]['prenom'];
    $credit=number_format($p->elements[0]['congesCredit'], 2, '.', ' ');
    $reliquat=number_format($p->elements[0]['congesReliquat'], 2, '.', ' ');
    $anticipation=number_format($p->elements[0]['congesAnticipation'], 2, '.', ' ');
    $credit2 = heure4($credit);
    $reliquat2 = heure4($reliquat);
    $anticipation2 = heure4($anticipation);
    $recuperation=number_format($p->elements[0]['recupSamedi'], 2, '.', ' ');
    $recuperation2=heure4($recuperation);

    // Affichage du formulaire
    echo "<h3>Congés</h3>\n";
    echo "<form name='form' action='index.php' method='get' id='form' class='googleCalendarForm'>\n";
    echo "<input type='hidden' name='page' value='conges/modif.php' />\n";
    echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
    echo "<input type='hidden' name='menu' value='$menu' />\n";
    echo "<input type='hidden' name='confirm' value='confirm' />\n";
    echo "<input type='hidden' name='reliquat' value='$reliquat' />\n";
    echo "<input type='hidden' name='recuperation' value='$recuperation' />\n";
    echo "<input type='hidden' name='credit' value='$credit' />\n";
    echo "<input type='hidden' name='anticipation' value='$anticipation' />\n";
    echo "<input type='hidden' name='id' value='$id' id='id' />\n";
    echo "<input type='hidden' name='valide' value='0' />\n";
    echo "<input type='hidden' id='agent' value='{$_SESSION['login_nom']} {$_SESSION['login_prenom']}' />\n";
    echo "<table border='0'>\n";
    echo "<tr><td style='width:300px;'>\n";
    echo "Nom, prénom : \n";
    echo "</td><td>\n";
    if ($admin) {
        $db_perso=new db();
        $db_perso->query("select * from {$dbprefix}personnel where actif='Actif' order by nom,prenom;");
        echo "<select name='perso_id' id='perso_id' style='width:98%;' class='googleCalendarTrigger'>\n";
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
    echo "<input type='checkbox' name='allday' $allday onclick='all_day();'/>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "Date de début : \n";
    echo "</td><td>";
    echo "<input type='text' name='debut' id='debut' value='$debut' class='datepicker googleCalendarTrigger' style='width:97%;'/>\n";
    echo "</td></tr>\n";
    echo "<tr id='hre_debut' $displayHeures ><td>\n";
    echo "Heure de début : \n";
    echo "</td><td>\n";
    echo "<select name='hre_debut' id='hre_debut_select' style='width:98%;' class='googleCalendarTrigger'>\n";
    selectHeure(7, 23, true, $hre_debut);
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "<tr><td>\n";
    echo "Date de fin : \n";
    echo "</td><td>";
    echo "<input type='text' name='fin' id='fin' value='$fin'  class='datepicker googleCalendarTrigger' style='width:97%;'/>\n";
    echo "</td></tr>\n";
    echo "<tr id='hre_fin' $displayHeures ><td>\n";
    echo "Heure de fin : \n";
    echo "</td><td>\n";
    echo "<select name='hre_fin' id='hre_fin_select' style='width:98%;' class='googleCalendarTrigger' onfocus='setEndHour();'>\n";
    selectHeure(7, 23, true, $hre_fin);
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
    <td id='nbJours'>$jours</td></tr>

  <tr><td colspan='2' style='padding-top:20px;'>
EOD;
    if ($reliquat) {
        echo "Ces heures seront débitées sur le réliquat de l'année précédente puis sur : ";
    } else {
        echo "Ces heures seront débitées sur : ";
    }
    echo <<<EOD
    </td></tr>
    <tr><td>&nbsp;</td>
    <td><select name='debit' style='width:98%;' onchange='calculRestes();'>
    <option value='recuperation' $selectRecup >Le crédit de récupérations</option>
    <option value='credit' $selectCredit >Le crédit de congés de l'année en cours</option>
    </select></td></tr>
EOD;
    if (!$valide) {
        echo <<<EOD
    <tr><td colspan='2'>
      <table border='0'>
	<tr><td style='width:298px;'>Reliquat : </td><td style='width:130px;'>$reliquat2</td><td>(après débit : <font id='reliquat4'>$reliquat2</font>)</td></tr>
	<tr><td>Crédit de récupérations : </td><td>$recuperation2</td><td><font id='recup3'>(après débit : <font id='recup4'>$recuperation2</font>)</font></td></tr>
	<tr><td>Crédit de congés: </td><td>$credit2</td><td><font id='credit3'>(après débit : <font id='credit4'>$credit2</font>)</font></td></tr>
	<tr><td>Solde débiteur : </td><td>$anticipation2</td><td><font id='anticipation3'>(après débit : <font id='anticipation4'>$anticipation2</font>)</font></td></tr>
      </table>
    </td></tr>
EOD;
    }

    echo "<tr valign='top'><td style='padding-top:15px;'>\n";
    echo "Commentaires : \n";
    echo "</td><td style='padding-top:15px;'>\n";
    echo "<textarea name='commentaires' cols='16' rows='5' style='width:97%;'>{$data['commentaires']}</textarea>\n";
    echo "</td></tr><tr><td>&nbsp;\n";

    echo "<tr style='vertical-align:top;'><td style='padding-top:15px;padding-bottom:15px;'>\n";
    echo "Demande : \n";
    echo "</td><td style='padding-top:15px;padding-bottom:15px;'>\n";
    echo dateFr($data['saisie'], true);
    if ($data['saisie_par'] and $data['saisie_par']!=$data['perso_id']) {
        echo " par ".nom($data['saisie_par']);
    }
    echo "</td></tr>\n";

    echo "<tr><td>Validation</td>\n";
    // Affichage de l'état de validation dans un menu déroulant si l'agent a le droit de le modifié et si le congé n'est pas validé
    if (($adminN2 and !$valide) or ($admin and $data['valide']==0)) {
        echo "<td><select name='valide' style='width:98%;' onchange='afficheRefus(this);'>\n";
        echo "<option value='0'>&nbsp;</option>\n";
        echo "<option value='2' {$selectAccept[2]}>Accept&eacute; (En attente de validation hi&eacute;rarchique)</option>\n";
        echo "<option value='-2' {$selectAccept[3]}>Refus&eacute; (En attente de validation hi&eacute;rarchique)</option>\n";
        if ($adminN2) {
            echo "<option value='1' {$selectAccept[0]}>Accept&eacute;</option>\n";
            echo "<option value='-1' {$selectAccept[1]}>Refus&eacute;</option>\n";
        }
        echo "</select></td>\n";
    }
    // Affichage simple de l'état de validation si l'agent n'a pas le droit de le modifié ou si le congé est validé
    else {
        if ($data['valide']<0) {
            echo "<td>Refusé</td>";
        } elseif ($data['valide']>0) {
            echo "<td>Validé</td>";
        } elseif ($data['valide_n1']) {
            echo "<td>En attente de validation hi&eacute;rarchique</td>";
        } else {
            echo "<td>Demand&eacute;</td>";
        }
    }
    echo "</tr>\n";
    echo "<tr id='tr_refus' style='vertical-align:top;$displayRefus'><td>Motif du refus :</td>\n";
    echo "<td><textarea name='refus' cols='16' rows='5' style='width:100%;'>{$data['refus']}</textarea></td></tr>\n";
    echo "<tr><td>&nbsp;</td></tr>\n";

    echo "</td></tr><tr><td colspan='2' style='text-align:center;'>\n";
    if ($menu=="off") {
        echo "<input type='button' value='Annuler' onclick='popup_closed();' class='ui-button'/>";
    } else {
        echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php?page=conges/voir.php\";' class='ui-button'/>";
    }

    if ((!$valide and $admin) or ($data['valide']==0 and $data['valide_n1']==0)) {
        echo "<input type='button' value='Enregistrer les modifications' style='margin-left:20px;' class='ui-button' onclick='verifConges();'/>\n";
    }

    if ($admin) {
        echo "<input type='button' value='Supprimer' style='margin-left:20px;' onclick='supprimeConges()' class='ui-button'/>\n";
    }
  
    echo "<div id='google-calendar-div' class='inline'></div>\n";
    echo "</td></tr></table>\n";
    echo "</form>\n";

    // Calcul des crédits restant au chargement de la page
    echo "<script type='text/JavaScript'>calculRestes();</script>\n";
}
