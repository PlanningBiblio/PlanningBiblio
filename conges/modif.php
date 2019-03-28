<?php
/**
Planning Biblio, Plugin Congés
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/modif.php
Création : 1er août 2013
Dernière modification : 12 septembre 2018
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié <etienne.cavalie@unice.fr>

Description :
Fichier permettant voir ou de modifier un congé
Accessible par la page conges/voir.php
Inclus dans le fichier index.php
*/

require_once "class.conges.php";

use Model\Agent;

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

// Calcul des crédits de récupération disponibles lors de l'ouverture du formulaire (date du jour)
$c = new conges();
$balance = $c->calculCreditRecup($perso_id);

// Droits d'administration niveau 1 et niveau 2
$c = new conges();
$roles = $c->roles($perso_id, true);
list($adminN1, $adminN2) = $roles;


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
    $agent = $entityManager->find(Agent::class, $perso_id);
    $nom = $agent->nom();
    $prenom = $agent->prenom();

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
      $sujet = $lang['leave_subject_accepted_pending'];
      $notifications=3;
      break;
    case -2:
      $sujet = $lang['leave_subject_refused_pending'];
      $notifications=3;
      break;
  }

    // Choix des destinataires en fonction de la configuration
    if ($config['Absences-notifications-agent-par-agent']) {
        $a = new absences();
        $a->getRecipients2(null, $perso_id, $notifications, 600, $debutSQL, $finSQL);
        $destinataires = $a->recipients;
    } else {
        $c = new conges();
        $c->getResponsables($debutSQL, $finSQL, $perso_id);
        $responsables = $c->responsables;

        $a = new absences();
        $a->getRecipients($notifications, $responsables, $agent);
        $destinataires = $a->recipients;
    }

    // Message qui sera envoyé par email
    $message="$sujet : <br/><br/>$prenom $nom<br/>Début : $debut";
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

    $retour = ($config['Conges-Recuperations'] and $get['debit'] == 'recuperation') ? 'index.php?page=conges/voir.php&recup=1' : 'index.php?page=conges/voir.php' ;

    $msg=urlencode("Le congé a été modifié avec succès.");
    echo "<script type='text/JavaScript'>document.location.href=\"$retour&msg=$msg&msgType=success&msg2=$msg2&msg2Type=$msg2Type\"</script>\n";
} else {	// Formulaire
    $valide=$data['valide']>0?true:false;
    $selectAccept[0]=$data['valide']>0?"selected='selected'":null;
    $selectAccept[1]=$data['valide']<0?"selected='selected'":null;
    $selectAccept[2]=($data['valide_n1']>0 and $data['valide']==0)?"selected='selected'":null;
    $selectAccept[3]=($data['valide_n1']<0 and $data['valide']==0)?"selected='selected'":null;
    $displayRefus=$data['valide']>=0?"display:none;":null;
    $displayRefus = ($data['valide_n1'] <0 and ($adminN1 or $adminN2)) ? null : $displayRefus;
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
    $credit = number_format((float) $p->elements[0]['conges_credit'], 2, '.', ' ');
    $reliquat = number_format((float) $p->elements[0]['conges_reliquat'], 2, '.', ' ');
    $anticipation = number_format((float) $p->elements[0]['conges_anticipation'], 2, '.', ' ');
    $credit2 = heure4($credit, true);
    $reliquat2 = heure4($reliquat, true);
    $anticipation2 = heure4($anticipation, true);
    $recuperation = number_format((float) $balance[1], 2, '.', ' ');
    $recuperation2=heure4($recuperation, true);

    if ($balance[4] < 0) {
        $balance[4] = 0;
    }

    $retour = ($config['Conges-Recuperations'] and $data['debit'] == 'recuperation') ? 'index.php?page=conges/voir.php&amp;recup=1' : 'index.php?page=conges/voir.php' ;

    // Affichage du formulaire
    if ($config['Conges-Recuperations'] == 1 and $data['debit']=="recuperation") {
        echo "<h3>Demande récupérations</h3>\n";
    } else {
        echo "<h3>Demande de congés</h3>\n";
    }
    echo "<form name='form' action='index.php' method='get' id='form' class='googleCalendarForm'>\n";
    echo "<input type='hidden' name='page' value='conges/modif.php' />\n";
    echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
    echo "<input type='hidden' name='confirm' value='confirm' />\n";
    echo "<input type='hidden' name='reliquat' value='$reliquat' />\n";
    echo "<input type='hidden' name='recuperation' id='recuperation' value='$recuperation' />\n";
    echo "<input type='hidden' name='recuperation_prev' id='recuperation_prev' value='{$balance[4]}' />\n";
    echo "<input type='hidden' name='credit' value='$credit' />\n";
    echo "<input type='hidden' name='anticipation' value='$anticipation' />\n";
    echo "<input type='hidden' name='id' value='$id' id='id' />\n";
    echo "<input type='hidden' name='valide' value='0' />\n";
    echo "<input type='hidden' id='agent' value='{$_SESSION['login_nom']} {$_SESSION['login_prenom']}' />\n";
    echo "<input type='hidden' name='conges-recup' id='conges-recup' value='{$config['Conges-Recuperations']}' />\n";
    echo "<table border='0'>\n";
    echo "<tr><td style='width:350px;'>\n";
    echo "Nom, prénom : \n";
    echo "</td><td>\n";
    if ($adminN1 or $adminN2) {
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

    if (!$config['Conges-Recuperations'] or $data['debit']=="recuperation") {
        echo "<tr><td style='padding-top:15px;'>\n";
        echo "Journée(s) entière(s) : \n";
        echo "</td><td style='padding-top:15px;'>\n";
        echo "<input type='checkbox' name='allday' $allday onclick='all_day();'/>\n";
        echo "</td></tr>\n";
    }

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

  <tr><td colspan='2' style='padding-top:20px;'>
EOD;

    // Si les congés et les récupérations sont traités de la même façon (Conges-Recuperations = Assembler), l'utilisateur peut choisir quel compteur sera débité
    if ($config['Conges-Recuperations'] == 0) {
        if ($reliquat != '0.00') {
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

    // Si les congés et les récupérations ne sont pas traités de la même façon (Conges-Recuperations = Dissocier), le compteur "congés" sera débité
    } else {
        if ($data['debit']=="credit") {
            if ($reliquat != '0.00') {
                echo "Ces heures seront débitées sur le réliquat de l'année précédente puis sur les crédits de congés de l'année en cours.";
            } else {
                echo "Ces heures seront débitées sur les crédits de congés de l'année en cours.";
            }
            echo "<input type='hidden' name='debit' value='credit' />\n";
            echo "</td></tr>\n";
        } else {
            echo "Ces heures seront débitées sur les crédits de récupérations.";
            echo "<input type='hidden' name='debit' value='recuperation' />\n";
            echo "</td></tr>\n";
        }
    }

    if (!$valide) {
        echo "<tr><td colspan='2'>\n";
        echo "<table border='0'>\n";
        if ($config['Conges-Recuperations'] == 0) {
            echo "<tr><td style='width:348px;'>Reliquat : </td><td style='width:130px;'>$reliquat2</td><td>(après débit : <font id='reliquat4'>$reliquat2</font>)</td></tr>\n";
            echo "<tr class='balance_tr'><td>Crédit de récupérations disponible au <span class='balance_date'>".dateFr($balance[0])."</span> : </td>\n";
            echo "<td id='balance_before'>".heure4($balance[1])."</td>\n";
            echo "<td>(après débit : <span id='recup4'>".heure4($balance[1], true)."</span>)</td></tr>\n";

            echo "<tr class='balance_tr'><td>Crédit de récupérations prévisionnel<sup>*</sup> au <span class='balance_date'>".dateFr($balance[0])."</span> : </td>\n";
            echo "<td id='balance2_before'>".heure4($balance[4], true)."</td>\n";
            echo "<td>(après débit : <span id='balance2_after'>".heure4($balance[4], true)."</span>)</td></tr>\n";

            echo "<tr><td>Crédit de congés: </td><td>$credit2</td><td><font id='credit3'>(après débit : <font id='credit4'>$credit2</font>)</font></td></tr>\n";
            echo "<tr><td>Solde débiteur : </td><td>$anticipation2</td><td><font id='anticipation3'>(après débit : <font id='anticipation4'>$anticipation2</font>)</font></td></tr>\n";
        } else {
            if ($data['debit']=="credit") {
                echo "<tr><td style='width:348px;'>Reliquat : </td><td style='width:130px;'>$reliquat2</td><td>(après débit : <font id='reliquat4'>$reliquat2</font>)</td></tr>\n";
                echo "<tr><td>Crédit de congés: </td><td>$credit2</td><td><font id='credit3'>(après débit : <font id='credit4'>$credit2</font>)</font></td></tr>\n";
                echo "<tr><td>Solde débiteur : </td><td>$anticipation2</td><td><font id='anticipation3'>(après débit : <font id='anticipation4'>$anticipation2</font>)</font></td></tr>\n";
            } else {
                echo "<tr class='balance_tr'><td style='width:348px;'>Solde disponible au <span class='balance_date'>".dateFr($balance[0])."</span> : </td>\n";
                echo "<td id='balance_before'>".heure4($balance[1], true)."</td>\n";
                echo "<td>(après débit : <span id='recup4'>".heure4($balance[1], true)."</span>)</td></tr>\n";

                echo "<tr class='balance_tr'><td>Solde prévisionnel<sup>*</sup> au <span class='balance_date'>".dateFr($balance[0])."</span> : </td>\n";
                echo "<td id='balance2_before'>".heure4($balance[4], true)."</td>\n";
                echo "<td>(après débit : <span id='balance2_after'>".heure4($balance[4], true)."</span>)</td></tr>\n";
            }
        }
        echo "</table>\n";
        echo "</td></tr>\n";
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

    // Si droit de validation niveau 2 sans avoir le droit de validation niveau 1, on affiche l'état de validation niveau 1
    if ($adminN2 and !$adminN1) {
        if ($data['valide_n1'] == 0) {
            $validation_n1 = "Congé demandé";
        } elseif ($data['valide_n1'] > 0) {
            $validation_n1 = "Congé accepté au niveau 1";
        } else {
            $validation_n1 = "Congé refusé au niveau 1";
        }

        echo "<tr><td>Validation niveau 1</td>\n";
        echo "<td>$validation_n1</td></tr>\n";
    }

    echo "<tr><td>Validation</td>\n";

    // Affichage de l'état de validation dans un menu déroulant si l'agent a le droit de le modifié et si le congé n'est pas validé

    if (($adminN2 and !$valide) or ($adminN1 and $data['valide']==0)) {
        echo "<td><select name='valide' id='validation' style='width:98%;' onchange='afficheRefus(this);'>\n";
        echo "<option value='0'>&nbsp;</option>\n";
        if ($adminN1) {
            echo "<option value='2' {$selectAccept[2]}>{$lang['leave_dropdown_accepted_pending']}</option>\n";
            echo "<option value='-2' {$selectAccept[3]}>{$lang['leave_dropdown_refused_pending']}</option>\n";
        }
        if ($adminN2 and ($data['valide_n1'] > 0 or $config['Conges-Validation-N2'] == 0)) {
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
    echo "<input type='button' value='Annuler' onclick='document.location.href=\"$retour\";' class='ui-button'/>";

    // Si le congé n'est pas validé (ni en niveau 1, ni en niveau 2) : Enregistrement autorisé par l'agent ou par les admins (niveau 1 ou 2)
    // Si le congé est validé en niveau 1 : Enregistrement autorisé pour les admins seulement (niveau 1 ou 2)
    // Si le congé est validé en niveau 2 : Enregistrement impossible
    if ((!$valide and ($adminN1 or $adminN2)) or ($data['valide']==0 and $data['valide_n1']==0)) {
        echo "<input type='button' value='Enregistrer les modifications' style='margin-left:20px;' class='ui-button' onclick='verifConges();'/>\n";
    }

    // Suppression par un admin niveau 1 autorisée si le congés n'a pas été validé par un niveau 2
    // Suppression autorisée par un admin niveau 2 dans tous les cas
    if (($adminN1 and $data['valide']==0) or $adminN2) {
        echo "<input type='button' value='Supprimer' style='margin-left:20px;' onclick='supprimeConges(\"$retour\")' class='ui-button'/>\n";
    }
  
    echo "<div id='google-calendar-div' class='inline'></div>\n";
    echo "</td></tr>\n";

    if ($config['Conges-Recuperations'] == 0) {
        echo "<tr><td colspan='2' style='padding-top:30px; font-style:italic;'><sup>*</sup> Le crédit de récupérations prévisionnel tient compte des demandes non validées (crédits et utilisations).</td></tr>\n";
    } elseif ($data['debit'] == 'recuperation') {
        echo "<tr><td colspan='2' style='padding-top:30px; font-style:italic;'><sup>*</sup> Le solde prévisionnel tient compte des demandes des récupérations non validées (crédits et utilisations).</td></tr>\n";
    }

    echo "</table>\n";
    echo "</form>\n";

    // Calcul des crédits restant au chargement de la page
    echo "<script type='text/JavaScript'>calculRestes();</script>\n";
}
