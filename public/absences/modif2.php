<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/absences/modif2.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Page validant la modification d'une absence : enregistrement dans la BDD des modifications

Page appelée par la page index.php
Page d'entrée : absences/modif.php

Variables
$agents_tous : tous les agents enregistrés dans l'application
$agents : les agents qui étaient enregistrés dans l'absence avant la modification
$agents_concernes : tous les agents concernés par la modification de l'absences, y compris les agents supprimés (les anciens, les nouveaux, les supprimés)
*/


require_once "class.absences.php";
require_once "personnel/class.personnel.php";

use App\Model\Agent;
use App\Model\AbsenceReason;

// Initialisation des variables
$commentaires=trim(filter_input(INPUT_GET, "commentaires", FILTER_SANITIZE_STRING));
$CSRFToken=trim(filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING));
$debut=filter_input(INPUT_GET, "debut", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_input(INPUT_GET, "fin", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$hre_debut=filter_input(INPUT_GET, "hre_debut", FILTER_CALLBACK, array("options"=>"sanitize_time"));
$hre_fin=filter_input(INPUT_GET, "hre_fin", FILTER_CALLBACK, array("options"=>"sanitize_time_end"));
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$motif=filter_input(INPUT_GET, "motif", FILTER_SANITIZE_STRING);
$motif_autre=trim(filter_input(INPUT_GET, "motif_autre", FILTER_SANITIZE_STRING));
$valide=filter_input(INPUT_GET, "valide", FILTER_SANITIZE_NUMBER_INT);
$groupe=filter_input(INPUT_GET, "groupe", FILTER_SANITIZE_STRING);
$rrule=filter_input(INPUT_GET, "rrule", FILTER_SANITIZE_STRING);
$recurrenceModif=filter_input(INPUT_GET, "recurrence-modif", FILTER_SANITIZE_STRING);

// perso_ids est un tableau de 1 ou plusieurs ID d'agent. Complété même si l'absence ne concerne qu'une personne
$perso_ids=$_GET['perso_ids'];
$perso_ids=filter_var_array($perso_ids, FILTER_SANITIZE_NUMBER_INT);

// Création du groupe si plusieurs agents et que le groupe n'est pas encore créé
if (count($perso_ids)>1 and !$groupe) {
    // ID du groupe (permet de regrouper les informations pour affichage en une seule ligne et modification du groupe)
    $groupe=time()."-".rand(100, 999);
}

// Pièces justificatives
$pj1=filter_input(INPUT_GET, "pj1", FILTER_CALLBACK, array("options"=>"sanitize_on01"));
$pj2=filter_input(INPUT_GET, "pj2", FILTER_CALLBACK, array("options"=>"sanitize_on01"));
$so=filter_input(INPUT_GET, "so", FILTER_CALLBACK, array("options"=>"sanitize_on01"));

$fin = $fin ? $fin : $debut;

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);
$debut_sql=$debutSQL." ".$hre_debut;
$fin_sql=$finSQL." ".$hre_fin;

// Récupération des informations des agents concernés par l'absence avant sa modification
// ET autres informations concernant l'absence avant modification
$a=new absences();
$a->fetchById($id);
$agents=$a->elements['agents'];
$commentaires1 = $a->elements['commentaires'];
$debut1=$a->elements['debut'];
$fin1=$a->elements['fin'];
$motif1 = $a->elements['motif'];
$motif_autre1 = $a->elements['motif_autre'];
$perso_ids1=$a->elements['perso_ids'];
$pj1_1=$a->elements['pj1'];
$pj2_1=$a->elements['pj2'];
$so_1=$a->elements['so'];
$rrule1 = $a->elements['rrule'];
$uid = $a->elements['uid'];
$valide1_n1=$a->elements['valide_n1'];
$valide1_n2=$a->elements['valide_n2'];
$validation1_n1=$a->elements['validation_n1'];
$validation1_n2=$a->elements['validation_n2'];

if ($valide1_n2 > 0) {
    $valide1 = 1;
} elseif ($valide1_n2 < 0) {
    $valide1 = -1;
} elseif ($valide1_n1 > 0) {
    $valide1 = 2;
} elseif ($valide1_n1 < 0) {
    $valide1 = -2;
} else {
    $valide1 = 0;
}

// Si l'absence est importée depuis un agenda extérieur, on interdit la modification
$iCalKey=$a->elements['ical_key'];
$cal_name=$a->elements['cal_name'];
if ($iCalKey and substr($cal_name, 0, 23) != 'PlanningBiblio-Absences') {
    include "include/accessDenied.php";
}

// Récuperation des informations des agents concernés par l'absence après sa modification (agents sélectionnés)
$p=new personnel();
$p->supprime = array(0,1,2);
$p->responsablesParAgent = true;
$p->fetch();
$agents_tous = $p->elements;

// Tous les agents
foreach ($agents_tous as $elem) {
    if (in_array($elem['id'], $perso_ids)) {
        $agents_selectionnes[$elem['id']] = $elem;
    }
}

// Tous les agents concernés (ajoutés, supprimés, restants)
$agents_concernes=array();
// Ajoute au tableau $agents_concernes les agents qui étaient présents avant la modification
foreach ($agents as $elem) {
    if (!array_key_exists($elem['perso_id'], $agents_concernes)) {
        $agents_concernes[$elem['perso_id']] = $agents_tous[$elem['perso_id']];
    }
}

// Ajoute au tableau $agents_concernes les agents sélectionnés
foreach ($agents_selectionnes as $elem) {
    if (!array_key_exists($elem['id'], $agents_concernes)) {
        $agents_concernes[$elem['id']] = $elem;
    }
}

// Les agents supprimés de l'absence
$agents_supprimes=array();
foreach ($agents as $elem) {
    if (!array_key_exists($elem['perso_id'], $agents_selectionnes)) {
        $agents_supprimes[$elem['perso_id']] = $agents_tous[$elem['perso_id']];
    }
}

// Les agents ajoutés à l'absence
$agents_ajoutes=array();
foreach ($agents_selectionnes as $elem) {
    if (!in_array($elem['id'], $perso_ids1)) {
        $agents_ajoutes[]=$elem;
    }
}

// Comparaison des anciennes données et des nouvelles
$modification = (
  !empty($agents_ajoutes)
  or !empty($agents_supprimes)
  or $debut1 != $debut_sql
  or $fin1 != $fin_sql
  or htmlentities($motif1, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false) != htmlentities($motif, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false)
  or htmlentities($motif_autre1, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false) != htmlentities($motif_autre, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false)
  or htmlentities($commentaires1, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false) != htmlentities($commentaires, ENT_QUOTES|ENT_IGNORE, 'UTF-8', false)
  or $valide1 != $valide
  or $rrule1 != $rrule
  or empty($pj1_1) != empty($pj1)
  or empty($pj2_1) != empty($pj2)
  or empty($so_1) != empty($so)
  );

// Si aucune modification, on retourne directement à la liste des absences
if (!$modification) {
    $msg=urlencode("L'absence a été modifiée avec succès");
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=success';</script>\n";
}

// Sécurité
// Droit 6 = modification de ses propres absences
// Droit 9 = Droit d'enregistrer des absences pour d'autres agents
// Droits 20x = modification de toutes les absences (admin seulement)
// Droits 50x = validation N2

$acces = false;
for ($i = 1; $i <= $config['Multisites-nombre']; $i++) {
    if (in_array((200+$i), $droits) or in_array((500+$i), $droits)) {
        $acces = true;
    }
}

if (!$acces) {
    if ((in_array(6, $droits) and count($perso_ids) == 1 and in_array($_SESSION['login_id'], $perso_ids))
    or (in_array(9, $droits) and in_array(6, $droits) and in_array($_SESSION['login_id'], $perso_ids))) {
        $acces = true;
    }
}

if (!$acces) {
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
    include "include/footer.php";
    exit;
}

// Définition des droits d'accès pour les administrateurs en multisites
// Multisites, ne pas modifier les absences si aucun agent n'appartient à un site géré
if ($config['Multisites-nombre']>1) {
    // $sites_agents comprend l'ensemble des sites en lien avec les agents concernés par cette modification d'absence
    $sites_agents=array();
    foreach ($agents_concernes as $elem) {
        if (is_array($elem['sites'])) {
            foreach ($elem['sites'] as $site) {
                if (!in_array($site, $sites_agents)) {
                    $sites_agents[]=$site;
                }
            }
        }
    }

    $admin = false;
    foreach ($sites_agents as $site) {
        if (in_array((200+$i), $droits) or in_array((500+$i), $droits)) {
            $admin = true;
            break;
        }
    }

    if (!$admin and !$acces) {
        echo "<h3>Modification de l'absence</h3>\n";
        echo "Vous n'êtes pas autorisé(e) à modifier cette absence.<br/><br/>\n";
        echo "<a href='index.php?page=absences/voir.php'>Retour à la liste des absences</a><br/><br/>\n";
        include "include/footer.php";
        exit;
    }
} else {
    $admin = in_array(201, $droits);
}


// Etats de validation. Par défaut, on met les états  initiaux (avant modification) pour conserver ces informations si aucun changement n'apparaît sur le champ "validation"
$valide_n1 = $valide1_n1;
$valide_n2 = $valide1_n2;
$validation_n1 = $validation1_n1;
$validation_n2 = $validation1_n2;

// On met à jour les infos seulement si une modification apparaît sur le champ "validation"de façon à garder l'horodatage initial ($valide != $valide1)
if ($config['Absences-validation'] and $valide != $valide1) {

  // Initialisation et retour à l'état demandé
    $valide_n1 = 0;
    $validation_n1 = '0000-00-00 00:00:00';
    $valide_n2 = 0;
    $validation_n2 = '0000-00-00 00:00:00';

    // Validation ou refus niveau 2
    if ($valide == 1 or $valide == -1) {
        $valide_n1 = $valide1_n1;
        $validation_n1 = $validation1_n1;
        $valide_n2 = $valide * $_SESSION['login_id'];
        $validation_n2 = date("Y-m-d H:i:s");
    }
    // Validation ou refus niveau 1
    elseif ($valide == 2 or $valide == -2) {
        $valide_n1 = ($valide/2) * $_SESSION['login_id'];
        $validation_n1 = date("Y-m-d H:i:s");
    }
}

// Modification d'une absence récurrente
if ($rrule) {

  // $nouvel_enregistrement permet de définir s'il y aura besoin d'un nouvel enregistrement dans le cas de l'ajout d'une exception ou de la modification des événements à venir
    $nouvel_enregistrement = false;

    switch ($recurrenceModif) {
    case 'current':

      // On ajoute une exception à l'événement ICS
      $exdate = preg_replace('/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/', "$1$2$3T$4$5$6", $debut1);

      foreach ($agents_concernes as $elem) {
          $a = new absences();
          $a->CSRFToken = $CSRFToken;
          $a->perso_id = $elem['id'];
          $a->uid = $uid;
          $a->ics_add_exdate($exdate);
      }

      // Un nouvel enregistrement sera créé pour l'occurence modifiée
      $nouvel_enregistrement = true;
      $rrule = false;

      break;

    case 'next':

      // On sépare les événemnts précédents des suivants

      // Récupère l'événement préalablement enregistré pour récupérer la date de début DTSTART et les dates d'exceptions EXDATE
      $a = new absences();
      $a->uid = $uid;
      $a->ics_get_event();
      $event = $a->elements;

      // Si la date de début correspond au premier événement de la série ($debut1 == DTSTART), la modification de l'occurence et des suivantes signifie la modification de toutes les occurrences
      // On modifie donc toutes les occurences comme si "all" avait été choisi et on quitte sans ajouter de nouvel enregistrement
      preg_match("/DTSTART.*:(\d*)/", $event, $matches);
      if (date('Ymd', strtotime($debut1)) == $matches[1]) {
          $a = new absences();
          $a->debut = $debut;
          $a->fin = $fin;
          $a->groupe = $groupe;
          $a->hre_debut = $hre_debut;
          $a->hre_fin = $hre_fin;
          $a->perso_ids = $perso_ids;
          $a->commentaires = $commentaires;
          $a->motif = $motif;
          $a->motif_autre = $motif_autre;
          $a->CSRFToken = $CSRFToken;
          $a->rrule = $rrule;
          $a->valide_n1 = $valide_n1;
          $a->valide_n2 = $valide_n2;
          $a->validation_n1 = $validation_n1;
          $a->validation_n2 = $validation_n2;
          $a->pj1 = $pj1;
          $a->pj2 = $pj2;
          $a->so = $so;
          $a->uid = $uid;
          $a->id = $id;
          $a->ics_update_event();

          $nouvel_enregistrement = false;

          break;
      }

      // On définie la date de fin de la première série. Cette date doit être sur le fuseau GMT
      // On commence par retirer une seconde de façon à ce que la première série s'arrête bien avant la deuxième
      $serie1_end = date('Ymd\THis', strtotime($debut1.' -1 second'));

      // Puis on récupère la date du fuseau GMT
      $datetime = new DateTime($serie1_end, new DateTimeZone(date_default_timezone_get()));
      $datetime->setTimezone(new DateTimeZone('GMT'));
      $serie1_end = $datetime->format('Ymd\THis\Z');

      // On met à jour la première série : modification de RRULE en mettant UNTIL à la date de fin
      foreach ($agents_concernes as $elem) {
          $a = new absences();
          $a->CSRFToken = $CSRFToken;
          $a->perso_id = $elem['id'];
          $a->uid = $uid;
          $a->ics_update_until($serie1_end);
      }


      // Un nouvel événement sera créé pour les occurences à venir
      $nouvel_enregistrement = true;

      // Si des exceptions existaient, on les réécrit dans le nouvel enregistrement
      if (strpos($event, 'EXDATE')) {
          preg_match("/(EXDATE.*\n)/", $event, $matches);
          $add_exdate = $matches[1];
      }

      // Si la fin de récurrence est définie par l'attribut COUNT, il doit être adapté. Les occurences antérieures à $serie1_end doivent être déduites.
      if (strpos($rrule, 'COUNT')) {
          // Récupération du nombre d'occurences antérieures à la date de l'événement choisi
          $db = new db();
          $db->select2('absences', 'debut', array('cal_name' => 'LIKEPlanningBiblio-Absences%', 'uid' => $uid, 'debut' => "<$debut1"), 'GROUP BY `debut`');
          $nb = $db->nb;

          // Récupération de la valeur initiale de COUNT
          preg_match('/COUNT=(\d*)/', $rrule, $matches);

          // Soustraction
          $count = $matches[1] - $nb;

          // Réécriture de la règle
          $rrule = preg_replace('/COUNT=(\d*)/', "COUNT=$count", $rrule);
      }

      break;

    case 'all':

      // On modifie toutes les occurences de l'événement.
      // Modification de l'événement ICS pour les agents qui en faisaient déjà partie, ajout pour les nouveaux, suppression pour les agents retirés

      $a = new absences();
      $a->debut = $debut;
      $a->fin = $fin;
      $a->groupe = $groupe;
      $a->hre_debut = $hre_debut;
      $a->hre_fin = $hre_fin;
      $a->perso_ids = $perso_ids;
      $a->commentaires = $commentaires;
      $a->motif = $motif;
      $a->motif_autre = $motif_autre;
      $a->CSRFToken = $CSRFToken;
      $a->rrule = $rrule;
      $a->valide_n1 = $valide_n1;
      $a->valide_n2 = $valide_n2;
      $a->validation_n1 = $validation_n1;
      $a->validation_n2 = $validation_n2;
      $a->pj1 = $pj1;
      $a->pj2 = $pj2;
      $a->so = $so;
      $a->uid = $uid;
      $a->id = $id;
      $a->ics_update_event();

      break;
  }

    if ($nouvel_enregistrement) {

    // On enregistre l'événement modifié dans la base de données, et dans les fichiers ICS si $rrule
        $a = new absences();
        $a->debut = $debut;
        $a->fin = $fin;
        $a->hre_debut = $hre_debut;
        $a->hre_fin = $hre_fin;
        $a->perso_ids = $perso_ids;
        $a->commentaires = $commentaires;
        $a->motif = $motif;
        $a->motif_autre = $motif_autre;
        $a->CSRFToken = $CSRFToken;
        $a->rrule = $rrule;
        if (!empty($add_exdate)) {
            $a->exdate = $add_exdate;
        }
        $a->valide = $valide;
        $a->pj1 = $pj1;
        $a->pj2 = $pj2;
        $a->so = $so;
        $a->uid = $uid;
        $a->id = $id;
        $a->add();
        $msg2 = $a->msg2;
        $msg2_type = $a->msg2_type;
    }
}

// Si pas de récurrence, modifiation des informations directement dan la base de données
else {

  // Mise à jour du champs 'absent' dans 'pl_poste'
    // Suppression du marquage absent pour tous les agents qui étaient concernés par l'absence avant sa modification
    // Comprend les agents supprimés et ceux qui restent
    /**
    * @note : le champ pl_poste.absent n'est plus mis à 1 lors de la validation des absences depuis la version 2.4
    * mais nous devons garder la mise à 0 pour la suppression ou modifications des absences enregistrées avant cette version.
    * NB : le champ pl_poste.absent est également utilisé pour barrer les agents depuis le planning, donc on ne supprime pas toutes ses valeurs
    */
    $ids=implode(",", $perso_ids1);
    $db=new db();
    $debut1=$db->escapeString($debut1);
    $fin1=$db->escapeString($fin1);
    $ids=$db->escapeString($ids);
    $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
    CONCAT(`date`,' ',`debut`) < '$fin1' AND CONCAT(`date`,' ',`fin`) > '$debut1'
    AND `perso_id` IN ($ids)";
    $db->query($req);


    // Préparation des données pour mise à jour de la table absence et insertion pour les agents ajoutés
    $data = array('motif' => $motif, 'motif_autre' => $motif_autre, 'commentaires' => $commentaires, 'debut' => $debut_sql, 'fin' => $fin_sql, 'groupe' => $groupe,
    'valide' => $valide_n2, 'validation' => $validation_n2, 'valide_n1' => $valide_n1, 'validation_n1' => $validation_n1);

    if (in_array(701, $droits)) {
        $data=array_merge($data, array("pj1"=>$pj1, "pj2"=>$pj2, "so"=>$so));
    }


    // Mise à jour de la table 'absences'
    // Sélection des lignes à modifier dans la base à l'aide du champ id car fonctionne également si le groupe n'existait pas au départ contrairement au champ groupe
    // (dans le cas d'une absence simple ou absence simple transformée en absence multiple).
    // Récupération de tous les ids de l'absence avant modification
    $ids=array();
    foreach ($agents as $agent) {
        $ids[]=$agent['absence_id'];
    }
    $ids=implode(",", $ids);
    $where=array("id"=>"IN $ids");

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("absences", $data, $where);


    // Ajout de nouvelles lignes dans la table absences si des agents ont été ajoutés
    $insert=array();
    foreach ($agents_ajoutes as $agent) {
        $insert[]=array_merge($data, array('perso_id'=>$agent['id']));
    }
    if (!empty($insert)) {
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->insert("absences", $insert);
    }


    // Suppresion des lignes de la table absences concernant les agents supprimés
    $agents_supprimes_ids=array();
    foreach ($agents_supprimes as $agent) {
        $agents_supprimes_ids[] = $agent['id'];
    }
    $agents_supprimes_ids=implode(",", $agents_supprimes_ids);

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("absences", array("id"=>"IN $ids", "perso_id"=>"IN $agents_supprimes_ids"));
}


// Envoi d'un mail de notification
$sujet="Modification d'une absence";

// Choix des destinataires des notifications selon le degré de validation
// Si pas de validation, la notification est envoyée au 1er groupe
if ($config['Absences-validation']=='0') {
    $notifications=2;
} else {
    if ($valide1_n2<=0 and $valide_n2>0) {
        $sujet="Validation d'une absence";
        $notifications=4;
    } elseif ($valide1_n2>=0 and $valide_n2<0) {
        $sujet="Refus d'une absence";
        $notifications=4;
    } elseif ($valide1_n1<=0 and $valide_n1>0) {
        $sujet="Acceptation d'une absence (en attente de validation hiérarchique)";
        $notifications=3;
    } elseif ($valide1_n1>=0 and $valide_n1<0) {
        $sujet="Refus d'une absence (en attente de validation hiérarchique)";
        $notifications=3;
    } else {
        $sujet="Modification d'une absence";
        $notifications=2;
    }
}

$workflow = 'A';
$entityManager = $GLOBALS['entityManager'];
$reason = $entityManager->getRepository(AbsenceReason::class)->findoneBy(['valeur' => $motif]);
if ($reason) {
    $workflow = $reason->notification_workflow();
}
$notifications = "-$workflow$notifications";

// Liste des responsables
// Pour chaque agent, recherche des responsables absences

/** Si le paramètre "Absences-notifications-agent-par-agent" est coché,
 * les notifications de modification d'absence sans validation sont envoyés aux responsables enregistrés dans dans la page Validations / Notifications
 * Les absences validées au niveau 1 sont envoyés aux agents ayant le droit de validation niveau 2
 * Les absences validées au niveau 2 sont envoyés aux agents concernés par l'absence
 */
 
if ($config['Absences-notifications-agent-par-agent']) {
    $a=new absences();
    $a->getRecipients2($agents_tous, $agents_concernes, $notifications, 500, $debutSQL, $finSQL);
    $destinataires = $a->recipients;
} else {
    $responsables=array();
    foreach ($agents_concernes as $agent) {
        $a=new absences();
        $a->getResponsables($debutSQL, $finSQL, $agent['id']);
        $responsables=array_merge($responsables, $a->responsables);
    }

    // Pour chaque agent, recherche des destinataires de notification en fonction de la config. (responsables absences, responsables directs, agent).
    $ids = array_column($agents, 'perso_id'); 
    $staff_members = $entityManager->getRepository(Agent::class)->findById($ids);
    $destinataires=array();
    foreach ($staff_members as $member) {
        $a=new absences();
        $a->getRecipients($notifications, $responsables, $member);
        $destinataires=array_merge($destinataires, $a->recipients);
    }

    // Suppresion des doublons dans les destinataires
    $tmp=array();
    foreach ($destinataires as $elem) {
        if (!in_array($elem, $tmp)) {
            $tmp[]=$elem;
        }
    }
    $destinataires=$tmp;
}

// Recherche des plages de SP concernées pour ajouter cette information dans le mail.
$a=new absences();
$a->debut=$debut_sql;
$a->fin=$fin_sql;
$a->perso_ids=$perso_ids;
$a->infoPlannings();
$infosPlanning=$a->message;

// Message
usort($agents_selectionnes, "cmp_prenom_nom");
usort($agents_supprimes, "cmp_prenom_nom");

$message="<b><u>$sujet</u></b> :";
$message.="<ul><li>";
if ((count($agents_selectionnes) + count($agents_supprimes)) >1) {
    $message.="Agents :<ul>\n";
    foreach ($agents_selectionnes as $agent) {
        $message.="<li><strong>{$agent['prenom']} {$agent['nom']}</strong></li>\n";
    }
    foreach ($agents_supprimes as $agent) {
        $message.="<li><span class='striped'>{$agent['prenom']} {$agent['nom']}</span></li>\n";
    }
    $message.="</ul>\n";
} else {
    $message.="Agent : <strong>{$agents_selectionnes[0]['prenom']} {$agents_selectionnes[0]['nom']}</strong>\n";
}
$message.="</li>\n";

$message.="<li>Début : <strong>$debut";
if ($hre_debut!="00:00:00") {
    $message.=" ".heure3($hre_debut);
}
$message.="</strong></li><li>Fin : <strong>$fin";
if ($hre_fin!="23:59:59") {
    $message.=" ".heure3($hre_fin);
}
$message.="</strong></li>";

if ($rrule) {
    $rruleText = recurrenceRRuleText($rrule);
    $message .= "<li>Récurrence : $rruleText</li>";
}

$message.="<li>Motif : $motif";
if ($motif_autre) {
    $message.=" / $motif_autre";
}
$message.="</li>";

if ($config['Absences-validation']) {
    $validationText="Demand&eacute;e";
    if ($valide_n2>0) {
        $validationText="Valid&eacute;e";
    } elseif ($valide_n2<0) {
        $validationText="Refus&eacute;e";
    } elseif ($valide_n1>0) {
        $validationText="Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
    } elseif ($valide_n1<0) {
        $validationText="Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
    }

    $message.="<li>Validation : $validationText</li>\n";
}

if ($commentaires) {
    $message.="<li>Commentaire:<br/>$commentaires</li>";
}
$message.="</ul>";

// Ajout des informations sur les plannings
$message.=$infosPlanning;

// Ajout du lien permettant de rebondir sur l'absence
$url=createURL("absences/modif.php&id=$id");
$message.="<br/><br/>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a><br/><br/>";

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

  
$msg=urlencode("L'absence a été modifiée avec succès");
echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=success&msg2=$msg2&msg2Type=$msg2Type';</script>\n";
