<?php
/**
Planning Biblio, Version 2.7.08
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : absences/class.absences.php
Création : mai 2011
Dernière modification : 14 décembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe absences : contient les fonctions de recherches des absences

Page appelée par les autres pages du dossier absences
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  require_once __DIR__."/../include/accessDenied.php";
}

require_once __DIR__."/../ics/class.ics.php";
require_once __DIR__."/../personnel/class.personnel.php";


class absences{
  public $agents_supprimes=array(0);
  public $CSRFToken=null;
  public $commentaires=null;
  public $debut=null;
  public $dtstamp=null;
  public $edt=array();
  public $elements=array();
  public $error=false;
  public $fin=null;
  public $groupe=null;
  public $heures=0;
  public $heures2=null;
  public $hre_debut = null;
  public $hre_fin = null;
  public $ignoreFermeture=false;
  public $minutes=0;
  public $motif = null;
  public $motif_autre = null;
  public $perso_id=null;
  public $perso_ids=array();
  public $recipients=array();
  public $rrule = null;
  public $validation_n1 = null;
  public $validation_n2 = null;
  public $valide=false;
  public $valide_n1 = null;
  public $valide_n2 = null;
  public $uid=null;
  public $unique=false;
  public $update_db = false;

  public function __construct(){
  }

  
  /** @function add()
   * Enregistre une nouvelle absence dans la base de données, créé les fichiers ICS pour les absences récurrentes (appel de la methode ics_add_event), envoie les notifications
   * @params : tous les éléments nécessaires à la création d'une absence
   * @return : message d'erreur ou de succès de l'enregistrement et de l'envoi des notifications
   */
  public function add(){
    $debut = $this->debut;
    $fin = $this->fin;
    $hre_debut = $this->hre_debut;
    $hre_fin = $this->hre_fin;
    $perso_ids = $this->perso_ids;
    $commentaires = $this->commentaires;
    $motif = $this->motif;
    $motif_autre = $this->motif_autre;

    $fin = $fin ? $fin : $debut;

    $debutSQL = dateSQL($debut);
    $finSQL = dateSQL($fin);

    // Validation
    // Validation, valeurs par défaut
    $valide_n1 = 0;
    $valide_n2 = 0;
    $validation_n1 = "0000-00-00 00:00:00";
    $validation_n2 = "0000-00-00 00:00:00";
    $validationText = "Demand&eacute;e";

    // Si le workflow est désactivé, absence directement validée
    if(!$GLOBALS['config']['Absences-validation']){
      $valide_n2 = 1;
      $validation_n2 = date("Y-m-d H:i:s");
      $validationText = null;
    }
    // Si workflow, validation en fonction de $this->valide
    else{
      switch($this->valide){
        case 1 :
          $valide_n2 = $_SESSION['login_id'];
          $validation_n2 = date("Y-m-d H:i:s");
          $validationText = "Valid&eacute;e";
          break;
          
        case -1 :
          $valide_n2 = $_SESSION['login_id']*-1;
          $validation_n2 = date("Y-m-d H:i:s");
          $validationText = "Refus&eacute;e";
          break;
          
        case 2 :
          $valide_n1 = $_SESSION['login_id'];
          $validation_n1 = date("Y-m-d H:i:s");
          $validationText = "Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
          break;
          
        case -2 :
          $valide_n1 = $_SESSION['login_id']*-1;
          $validation_n1 = date("Y-m-d H:i:s");
          $validationText = "Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
          break;
      }
    }

    // Choix des destinataires des notifications selon le degré de validation
    $notifications = 1;
    if($GLOBALS['config']['Absences-validation'] and $valide_n1 != 0){
      $notifications = 3;
    }
    elseif($GLOBALS['config']['Absences-validation'] and $valide_n2 != 0){
      $notifications=4;
    }

    // Formatage des dates/heures de début/fin pour les requêtes SQL
    $debut_sql = $debutSQL.' '.$hre_debut;
    $fin_sql = $finSQL.' '.$hre_fin;

    // Si erreur d'envoi de mail, affichage de l'erreur (Initialisation des variables)
    $msg2=null;
    $msg2_type=null;

    // ID du groupe (permet de regrouper les informations pour affichage en une seule ligne et modification du groupe)
    $groupe = (count($perso_ids) > 1) ? time().'-'.rand(100,999) : null;

    // On définie le dtstamp avant la boucle, sinon il différe selon les agents, ce qui est problématique pour retrouver les événéments des membres d'un groupe pour les modifications car le DTSTAMP est intégré dans l'UID
    $dtstamp = gmdate('Ymd\THis\Z');

    // Pour chaque agents
    foreach($perso_ids as $perso_id){
      // Enregistrement des récurrences
      // Les événements récurrents sont enregistrés dans un fichier ICS puis importés dans la base de données
      // La méthode absences::ics_add_event se charge de créer le fichier et d'enregistrer les infos dans la base de données
      if($this->rrule){
        // Création du fichier ICS
        $a = new absences();
        $a->CSRFToken = $this->CSRFToken;
        $a->dtstamp = $dtstamp;
        $a->perso_id = $perso_id;
        $a->commentaires = $commentaires;
        $a->debut = $debut;
        $a->fin = $fin;
        $a->hre_debut = $hre_debut;
        $a->hre_fin = $hre_fin;
        $a->groupe = $groupe;
        $a->motif = $motif;
        $a->motif_autre = $motif_autre;
        $a->rrule = $this->rrule;
        $a->valide_n1 = $valide_n1;
        $a->valide_n2 = $valide_n2;
        $a->validation_n1 = $validation_n1;
        $a->validation_n2 = $validation_n2;
        $a->ics_add_event();

      // Les événements sans récurrence sont enregistrés directement dans la base de données
      } else {
        // Ajout de l'absence dans la table 'absence'
        $insert = array("perso_id"=>$perso_id, "debut"=>$debut_sql, "fin"=>$fin_sql, "motif"=>$motif, "motif_autre"=>$motif_autre, "commentaires"=>$commentaires, 
        "demande"=>date("Y-m-d H:i:s"), "pj1"=>$this->pj1, "pj2"=>$this->pj2, "so"=>$this->so, "groupe"=>$groupe);

        if($valide_n1 != 0){
          $insert["valide_n1"] = $valide_n1;
          $insert["validation_n1"] = $validation_n1;
        }
        else{
          $insert["valide"]=$valide_n2;
          $insert["validation"]=$validation_n2;
        }

        $db = new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->insert("absences", $insert);
      }

      // Recherche du responsables pour l'envoi de notifications
      $a = new absences();
      $a->getResponsables($debutSQL,$finSQL,$perso_id);
      $responsables = $a->responsables;

      // Informations sur l'agent
      $p = new personnel();
      $p->fetchById($perso_id);
      $nom = $p->elements[0]['nom'];
      $prenom = $p->elements[0]['prenom'];
      $mail = $p->elements[0]['mail'];
      $mails_responsables = $p->elements[0]['mails_responsables'];

      // Choix des destinataires des notifications selon la configuration
      $a = new absences();
      $a->getRecipients($notifications,$responsables,$mail,$mails_responsables);
      $destinataires = $a->recipients;
      

      // Récupération de l'ID de l'absence enregistrée pour la création du lien dans le mail
      $info = array(array("name"=>"MAX(id)", "as"=>"id"));
      $where = array("debut"=>$debut_sql, "fin"=>$fin_sql, "perso_id"=>$perso_id);
      $db = new db();
      $db->select2("absences", $info, $where);
      if($db->result){
        $id = $db->result[0]['id'];
      }

      // Recherche des plages de SP concernées pour ajouter cette information dans le mail.
      $a = new absences();
      $a->debut = $debut_sql;
      $a->fin = $fin_sql;
      $a->perso_ids = $perso_ids;
      $a->infoPlannings();
      $infosPlanning = $a->message;

      // N'envoie la notification que s'il s'agit d'un ajout simple, et non s'il s'agit d'un ajout qui suit la modification d'une récurrrence (exception ou modification des événements suivants sans modifier les précédents)
      // Si $this->uid : Ajout simple. Si !$this->uid : Modification, donc pas d'envoi de notification à ce niveau (envoyée via modif2.php)
      if(!$this->uid){
        // Titre différent si titre personnalisé (config) ou si validation ou non des absences (config)
        if($GLOBALS['config']['Absences-notifications-titre']){
          $titre = $GLOBALS['config']['Absences-notifications-titre'];
        }else{
          $titre = $GLOBALS['config']['Absences-validation'] ? "Nouvelle demande d absence" : "Nouvelle absence";
        }

        // Si message personnalisé (config), celui-ci est inséré
        if($GLOBALS['config']['Absences-notifications-message']){
          $message = "<b><u>{$GLOBALS['config']['Absences-notifications-message']}</u></b><br/>";
        }else{
          $message = "<b><u>$titre</u></b> : ";
        }

        // On complète le message avec les informations de l'absence
        $message .= "<ul><li>Agent : <strong>$prenom $nom</strong></li>";
        $message .= "<li>Début : <strong>$debut";
        if($hre_debut != "00:00:00")
          $message .= " ".heure3($hre_debut);
        $message .= "</strong></li><li>Fin : <strong>$fin";
        if($hre_fin != "23:59:59")
          $message .= " ".heure3($hre_fin);
        $message .= "</strong></li>";

        if($this->rrule){
          $rrule = recurrenceRRuleText($this->rrule);
          $message .= "<li>Récurrence : $rrule</li>";
        }

        $message .= "<li>Motif : $motif";
        if($motif_autre){
          $message .= " / $motif_autre";
        }
        $message .= "</li>";

        if($GLOBALS['config']['Absences-validation']){
          $message .= "<li>Validation : $validationText</li>\n";
        }

        if($commentaires){
          $message .= "<li>Commentaire: <br/>$commentaires</li>";
        }

        $message .= "</ul>";

        // Ajout des informations sur les plannings
        $message .= $infosPlanning;
        
        // Ajout du lien permettant de rebondir sur l'absence
        $url = createURL("absences/modif.php&id=$id");
        $message .= "<p>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a></p>";

        // Envoi du mail
        $m = new CJMail();
        $m->subject = $titre;
        $m->message = $message;
        $m->to = $destinataires;
        $m->send();

        // Si erreur d'envoi de mail
        if($m->error){
          $msg2 .= "<li>".$m->error_CJInfo."</li>";
          $msg2_type = "error";
        }
      }
      
    }
    $this->msg2 = $msg2;
    $this->msg2_type = $msg2_type;
  }

  /**
  * @function calculHeuresAbsences
  * @param date string, date de début au format YYYY-MM-DD
  * Calcule les heures d'absences des agents pour la semaine définie par $date ($date = une date de la semaine)
  * Utilisée par planning::menudivAfficheAgent pour ajuster le nombre d'heure de SP à effectuer en fonction des absences
  */
  public function calculHeuresAbsences($date){
    $config=$GLOBALS['config'];
    $version=$GLOBALS['version'];
    require_once __DIR__."/../include/horaires.php";
    require_once __DIR__."/../planningHebdo/class.planningHebdo.php";

    $d=new datePl($date);
    $dates=$d->dates;
    $semaine3=$d->semaine3;
    $j1=$dates[0];
    $j7=$dates[6];

    // Recherche des heures d'absences des agents pour cette semaine
    // Recherche si le tableau contenant les heures d'absences existe
    $db=new db();
    $db->select2("heures_absences","*",array("semaine"=>$j1));
    $heuresAbsencesUpdate=0;
    if($db->result){
      $heuresAbsencesUpdate=$db->result[0]["update_time"];
      $heures=json_decode(html_entity_decode($db->result[0]["heures"],ENT_QUOTES|ENT_IGNORE,"UTF-8"),true);
    }


    // Vérifie si la table absences a été mise à jour depuis le dernier calcul
    $aUpdate=strtotime($this->update_time());

    // Vérifie si la table personnel a été mise à jour depuis le dernier calcul
    $p=new personnel();
    $pUpdate=strtotime($p->update_time());

    // Vérifie si la table planning_hebdo a été mise à jour depuis le dernier calcul
    $p=new planningHebdo();
    $pHUpdate=strtotime($p->update_time());

    // Si la table absences ou la table personnel ou la table planning_hebdo a été modifiée depuis la création du tableaux des heures
    // Ou si le tableau des heures n'a pas été créé ($heuresAbsencesUpdate=0), on le (re)fait.
    if($aUpdate>$heuresAbsencesUpdate or $pUpdate>$heuresAbsencesUpdate or $pHUpdate>$heuresAbsencesUpdate){
      // Recherche de toutes les absences
      $absences=array();
      $a =new absences();
      $a->valide=true;
      $a->unique=true;
      $a->fetch(null,null,$j1,$j7,null);
      if($a->elements and !empty($a->elements)){
	$absences=$a->elements;
      }
      // Recherche de tous les plannings de présence
      $edt=array();
      $ph=new planningHebdo();
      $ph->debut=$j1;
      $ph->fin=$j7;
      $ph->valide=true;
      $ph->fetch();
      if($ph->elements and !empty($ph->elements)){
	$edt=$ph->elements;
      }

      // Recherche des agents pour appliquer le pourcentage sur les heures d'absences en fonction du taux de SP
      $p=new personnel();
      $p->fetch();
      $agents=$p->elements;
      
      // Calcul des heures d'absences
      $heures=array();
      if(!empty($absences)){
	// Pour chaque absence
	foreach($absences as $key => $value){
	  $perso_id=$value['perso_id'];
          $h1=array_key_exists($perso_id,$heures)?$heures[$perso_id]:0;
	  
	  // Si $h1 n'est pas un nombre ("N/A"), une erreur de calcul a été enregistrée. Donc on ne continue pas le calcul.
	  // $heures[$perso_id] restera "N/A"
	  if(!is_numeric($h1)){
	    continue;
	  }
	  
	  $a=new absences();
	  $a->debut=$value['debut'];
	  $a->fin=$value['fin'];
	  $a->perso_id=$perso_id;
	  $a->edt=$edt;
	  $a->ignoreFermeture=true;
	  $a->calculTemps2();

	  $h=$a->heures;
	  if(is_numeric($h)){
	    $h=$h+$h1;
	  }else{
	    $h="N/A";
	  }

	  $heures[$perso_id]=$h;

	}

        // On applique le pourcentage
        if(strpos($agents[$perso_id]["heures_hebdo"],"%")){
          $pourcent=(float) str_replace("%",null,$agents[$perso_id]["heures_hebdo"]);
          $heures[$perso_id]=$heures[$perso_id]*$pourcent/100;
        }
      }

      // Enregistrement des heures dans la base de données
      $db=new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->delete("heures_absences",array("semaine"=>$j1));
      $db=new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->insert("heures_absences",array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heures)));
    }

    return (array) $heures;
  }
  
  
  
  /**
  * @function calculTemps
  * @param debut string, date de début au format YYYY-MM-DD [H:i:s]
  * @param fin string, date de fin au format YYYY-MM-DD [H:i:s]
  * @param perso_id int, id de l'agent
  * Calcule le temps de travail d'un agent entre 2 dates.
  * Utilisé pour calculer le nombre d'heures correspondant à une absence
  * Ne calcule pas le temps correspondant aux jours de fermeture
  */
  public function calculTemps($debut,$fin,$perso_id){
    $version=$GLOBALS['config']['Version'];

    require_once __DIR__."/../joursFeries/class.joursFeries.php";

    $hre_debut=substr($debut,-8);
    $hre_fin=substr($fin,-8);
    $hre_fin=$hre_fin=="00:00:00"?"23:59:59":$hre_fin;
    $debut=substr($debut,0,10);
    $fin=substr($fin,0,10);

    // Calcul du nombre d'heures correspondant à une absence
    $current=$debut;
    $difference=0;

    // Pour chaque date
    while($current<=$fin){

      // On ignore les jours de fermeture
      $j=new joursFeries();
      $j->fetchByDate($current);
      if(!empty($j->elements)){
	foreach($j->elements as $elem){
	  if($elem['fermeture']){
	    $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
	    continue 2;
	  }
	}
      }

      $debutAbsence=$current==$debut?$hre_debut:"00:00:00";
      $finAbsence=$current==$fin?$hre_fin:"23:59:59";
      $debutAbsence=strtotime($debutAbsence);
      $finAbsence=strtotime($finAbsence);
      
      // On consulte le planning de présence de l'agent
      // On ne calcule pas les heures si le module planningHebdo n'est pas activé, le calcul serait faux si les emplois du temps avaient changé
      if(!$GLOBALS['config']['PlanningHebdo']){
	$this->error=true;
	$this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	return false;
      }

      // On consulte le planning de présence de l'agent
      $version=$GLOBALS['version'];
      require_once __DIR__."/../planningHebdo/class.planningHebdo.php";

      $p=new planningHebdo();
      $p->perso_id=$perso_id;
      $p->debut=$current;
      $p->fin=$current;
      $p->valide=true;
      $p->fetch();
      // Si le planning n'est pas validé pour l'une des dates, on retourne un message d'erreur et on arrête le calcul
      if(empty($p->elements)){
        $this->error=true;
        $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
        return false;
      }

      // Sinon, on calcule les heures d'absence
      $d=new datePl($current);
      $semaine=$d->semaine3;
      $jour=$d->position?$d->position:7;
      $jour=$jour+(($semaine-1)*7)-1;

      $temps = calculPresence($p->elements[0]['temps'], $jour);

      foreach($temps as $t){
        $t0 = strtotime($t[0]);
        $t1 = strtotime($t[1]);
        
        $debutAbsence1 = $debutAbsence > $t0 ? $debutAbsence : $t0;
        $finAbsence1 = $finAbsence < $t1 ? $finAbsence : $t1;
        if( $finAbsence1 > $debutAbsence1 ) {
          $difference += $finAbsence1 - $debutAbsence1;
        }
      }

      $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
    }

    $this->minutes=$difference/60;                                      // nombre de minutes (ex 2h30 => 150)
    $this->heures=$difference/3600;                                     // heures et centièmes (ex 2h30 => 2.50)
    $this->heures2=heure4(number_format($this->heures, 2, '.', ''));    // heures et minutes (ex: 2h30 => 2h30)

  }

  /**
  * @function calculTemps2
  * @param debut string, date de début au format YYYY-MM-DD [H:i:s]
  * @param fin string, date de fin au format YYYY-MM-DD [H:i:s]
  * @param edt array, tableau contenant les emplois du temps des agents
  * @param perso_id int, id de l'agent
  * @param ignoreFermeture boolean, default=false : ignorer les jours de fermeture
  * Calcule le temps de travail d'un agents entre 2 dates.
  * Utilisé pour calculer le nombre d'heures correspondant à une absence
  * Les heures de présences sont données en paramètre dans un tableau. Offre de meilleurs performance que la fonction calculTemps 
  * lorsqu'elle est executée pour plusieurs agents
  */
  public function calculTemps2(){
    $version=$GLOBALS['config']['Version'];

    require_once __DIR__."/../joursFeries/class.joursFeries.php";

    $debut=$this->debut;
    $edt=$this->edt;
    $fin=$this->fin;
    $perso_id=$this->perso_id;

    $hre_debut=substr($debut,-8);
    $hre_fin=substr($fin,-8);
    $hre_fin=$hre_fin=="00:00:00"?"23:59:59":$hre_fin;
    $debut=substr($debut,0,10);
    $fin=substr($fin,0,10);

    // Calcul du nombre d'heures correspondant à une absence
    $current=$debut;
    $difference=0;

    // Pour chaque date
    while($current<=$fin){
      // On ignore les jours de fermeture
      if(!$this->ignoreFermeture){
	$j=new joursFeries();
	$j->fetchByDate($current);
	if(!empty($j->elements)){
	  foreach($j->elements as $elem){
	    if($elem['fermeture']){
	      $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
	      continue 2;
	    }
	  }
	}
      }

      $debutAbsence=$current==$debut?$hre_debut:"00:00:00";
      $finAbsence=$current==$fin?$hre_fin:"23:59:59";
      $debutAbsence=strtotime($debutAbsence);
      $finAbsence=strtotime($finAbsence);

      // On consulte le planning de présence de l'agent
      // On ne calcule pas les heures si le module planningHebdo n'est pas activé, le calcul serait faux si les emplois du temps avaient changé
      if(!$GLOBALS['config']['PlanningHebdo']){
	$this->error=true;
	$this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	$this->minutes="N/A";
	$this->heures="N/A";
	$this->heures2="N/A";
	return false;
      }

      // On consulte le planning de présence de l'agent
      if($GLOBALS['config']['PlanningHebdo']){
        $version = $GLOBALS['version'];
        require_once __DIR__."/../planningHebdo/class.planningHebdo.php";

	$edt=array();
	if($this->edt and !empty($this->edt)){
	  foreach($this->edt as $elem){
	    if($elem['perso_id'] == $perso_id){
	      $edt=$elem;
	      break;
	    }
	  }
	}

	// Si le planning n'est pas validé pour l'une des dates, on retourne un message d'erreur et on arrête le calcul
	if(empty($edt)){
	  $this->error=true;
	  $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	  $this->minutes="N/A";
	  $this->heures="N/A";
	  $this->heures2="N/A";
	  return false;
	}

	// Sinon, on calcule les heures d'absence
	$d=new datePl($current);
	$semaine=$d->semaine3;
	$jour=$d->position?$d->position:7;
	$jour=$jour+(($semaine-1)*7)-1;
      }
      
      $temps = calculPresence($edt['temps'], $jour);

      foreach($temps as $t){
        $t0 = strtotime($t[0]);
        $t1 = strtotime($t[1]);
        
        $debutAbsence1 = $debutAbsence > $t0 ? $debutAbsence : $t0;
        $finAbsence1 = $finAbsence < $t1 ? $finAbsence : $t1;
        if( $finAbsence1 > $debutAbsence1 ) {
          $difference += $finAbsence1 - $debutAbsence1;
        }
      }

      $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
    }

    $this->minutes=$difference/60;                                      // nombre de minutes (ex 2h30 => 150)
    $this->heures=$difference/3600;                                     // heures et centièmes (ex 2h30 => 2.50)
    $this->heures2=heure4(number_format($this->heures, 2, '.', ''));    // heures et minutes (ex: 2h30 => 2h30)
  }

  
  /**
  * @method check
  * @param int $perso_id
  * @param string $debut, format YYYY-MM-DD HH:ii:ss
  * @param string $fin, format YYYY-MM-DD HH:ii:ss
  * @param boolean $valide, default = true
  * Contrôle si l'agent $perso_id est absent entre $debut et $fin
  * Retourne true si absent, false sinon
  * Si $valide==false, les absences non validées seront également prises en compte
  */
  public function check($perso_id,$debut,$fin,$valide=true){
  
    if(strlen($debut)==10){
      $debut.=" 00:00:00";
    }

    if(strlen($fin)==10){
      $fin.=" 23:59:59";
    }

    $filter=array("perso_id"=>$perso_id, "debut"=>"<$fin", "fin"=>">$debut");
    
    if($valide==true or $GLOBALS['config']['Absences-validation']==0){
      $filter["valide"]=">0";
    }
    
    $db=new db();
    $db->select2("absences",null,$filter);
    if($db->result){
      return true;
    }
    return false;
  }


  public function fetch($sort="`debut`,`fin`,`nom`,`prenom`",$agent=null,$debut=null,$fin=null,$sites=null){
  
    $filter="";
    //	DB prefix
    $dbprefix=$GLOBALS['config']['dbprefix'];
    // Date, debut, fin
    $date=date("Y-m-d");
    if($debut){
      $fin=$fin?$fin:$date;
      if(strlen($fin)==10){
	$fin=$fin." 23:59:59";
      }
      $dates="`debut`<='$fin' AND `fin`>='$debut'";
    }
    else{
      $dates="`fin`>='$date'";
    }

    if($this->valide and $GLOBALS['config']['Absences-validation']){
      $filter.=" AND `{$dbprefix}absences`.`valide`>0 ";
    }


    // N'affiche que les absences des agents non supprimés par défaut : $this->agents_supprimes=array(0);
    // Affiche les absences des agents supprimés si précisé : $this->agents_supprimes=array(0,1) ou array(0,1,2)
    $deletedAgents=join("','",$this->agents_supprimes);
    $filter.=" AND `{$dbprefix}personnel`.`supprime` IN ('$deletedAgents') ";

    // Sort
    $sort=$sort?$sort:"`debut`,`fin`,`nom`,`prenom`";

    //	Select All
    $req="SELECT `{$dbprefix}personnel`.`nom` AS `nom`, `{$dbprefix}personnel`.`prenom` AS `prenom`, "
      ."`{$dbprefix}personnel`.`id` AS `perso_id`, `{$dbprefix}personnel`.`sites` AS `sites`, "
      ."`{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}absences`.`debut` AS `debut`, "
      ."`{$dbprefix}absences`.`fin` AS `fin`, "
      ."`{$dbprefix}absences`.`motif` AS `motif`, `{$dbprefix}absences`.`commentaires` AS `commentaires`, "
      ."`{$dbprefix}absences`.`valide` AS `valide`, `{$dbprefix}absences`.`validation` AS `validation`, "
      ."`{$dbprefix}absences`.`valide_n1` AS `valide_n1`, `{$dbprefix}absences`.`validation_n1` AS `validation_n1`, "
      ."`{$dbprefix}absences`.`pj1` AS `pj1`, `{$dbprefix}absences`.`pj2` AS `pj2`, `{$dbprefix}absences`.`so` AS `so`, "
      ."`{$dbprefix}absences`.`demande` AS `demande`, `{$dbprefix}absences`.`groupe` AS `groupe`, "
      ."`{$dbprefix}absences`.`cal_name` AS `cal_name`, `{$dbprefix}absences`.`ical_key` AS `ical_key`, `{$dbprefix}absences`.`rrule` AS `rrule` "
      ."FROM `{$dbprefix}absences` INNER JOIN `{$dbprefix}personnel` "
      ."ON `{$dbprefix}absences`.`perso_id`=`{$dbprefix}personnel`.`id` "
      ."WHERE $dates $filter ORDER BY $sort;";
    $db=new db();
    $db->query($req);

    $all=array();
    $groupes=array();
    if($db->result){
      foreach($db->result as $elem){
      
        // Multisites, n'affiche que les agents des sites choisis
        if(!empty($sites)){
          if($GLOBALS['config']['Multisites-nombre'] > 1){
            $sitesAgent = json_decode(html_entity_decode($elem['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
          }else{
            $sitesAgent = array(1);
          }
          
          $keep = false;

          if(is_array($sitesAgent)){
            foreach($sites as $site){
              if(in_array($site, $sitesAgent)){
                $keep = true;
                break;
              }
            }
          }

          if($keep === false){
            continue;
          }
        }

        // Gestion des groupes : ajout des infos sur les autres agents et affichage d'une seule ligne si $this->groupe=true
        $groupe = null;
        if(!empty($elem['groupe'])){
          // Le groupe est complété de la date et heure de début et de fin pour qu'il soit unique pour chaque occurence (si récurrence)
          $groupe = $elem['groupe'].$elem['debut'].$elem['fin'];
        }
        
        // N'ajoute qu'une ligne pour les membres d'un groupe si $this->true
        if($this->groupe and $groupe and in_array($groupe,$groupes)){
          continue;
        }

        // Ajoute des infos sur les autres agents
        if($groupe){
	  // Pour ne plus afficher les membres du groupe par la suite
	  $groupes[]=$groupe;
	  
	  // Ajoute les ID des autres agents appartenant à ce groupe
	  $perso_ids=array();
	  $agents=array();
	  foreach($db->result as $elem2){
            $groupe2 = $elem2['groupe'].$elem2['debut'].$elem2['fin'];
            if($groupe2 == $groupe){
	      $perso_ids[]=$elem2['perso_id'];
	      $agents[]=$elem2['nom']." ".$elem2['prenom'];
	    }
	  }
	  $elem['perso_ids']=$perso_ids;
	  sort($agents);
	  $elem['agents']=$agents;
	}else{
	  $elem['perso_ids'][]=$elem['perso_id'];
	  $elem['agents'][]=$elem['nom']." ".$elem['prenom'];
	}

        // Le champ commentaires peut comporter des <br/> ou équivalents HTML lorsqu'il est importé depuis un fichier ICS. On les remplace par \n
        $elem['commentaires'] = str_replace(array('<br/>','&lt;br/&gt;'), "\n", $elem['commentaires']);

	$tmp=$elem;
	$debut=dateFr(substr($elem['debut'],0,10));
	$fin=dateFr(substr($elem['fin'],0,10));
	$debutHeure=substr($elem['debut'],-8);
	$finHeure=substr($elem['fin'],-8);
	if($debutHeure=="00:00:00" and $finHeure=="23:59:59"){
	  $debutHeure=null;
	  $finHeure=null;
	}
	else{
	  $debutHeure=heure2($debutHeure);
	  $finHeure=heure2($finHeure);
	}
	$tmp['debutAff']="$debut $debutHeure";
	$tmp['finAff']="$fin $finHeure";
	$all[]=$tmp;
      }
    }

    
    //	By default $result=$all
    $result=$all;
    
    //	If name, keep only matching results
    if(is_array($all) and $agent){
      $result=array();

      foreach($all as $elem){
        if(is_numeric($agent)){
          if(in_array($agent, $elem['perso_ids'])){
            $result[]=$elem;
          }
        } else {
          foreach($elem['agents'] as $a){
            if(pl_stristr($a,$agent)){
              $result[]=$elem;
            }
          }
        }
      }
    }
    
    // Filtre Unique : supprime les absences qui se chevauchent pour ne pas les compter plusieurs fois dans les calculs.
    // Ce filtre ne doit être utilisé que pour le calcul des heures et avec le filtre valide=true

    if($this->unique){
      usort($result, 'cmp_perso_debut_fin');
      $cles_a_supprimer = array();
      
      $last = 0;
      for($i=1; $i<count($result); $i++){
      
        // Comparaisons : différents cas de figures
        //   |-----------------------------|      $last
        //   |-----------------------------|      $i    debut[$i] = debut[$last] and fin[$i] = fin[$last]  --> debut[$i] >= debut[$last] and fin[$i] <= fin[$last]*  --> supprime $i
        //   |----------------------------------| $i    debut[$i] = debut[$last] and fin[$i] > fin[$last]  --> supprime $last
        //      |---------------------|           $i    debut[$i] > debut[$last] and fin[$i] < fin[$last]  --> debut[$i] >= debut[$last] and fin[$i] <= fin[$last]*  --> supprime $i
        //      |--------------------------|      $i    debut[$i] > debut[$last] and fin[$i] = fin[$last]  --> debut[$i] >= debut[$last] and fin[$i] <= fin[$last]*  --> supprime $i
        //      |-------------------------------| $i    debut[$i] > debut[$last] and fin[$i] > fin[$last]  --> fin[$last] = debut[$i], $i ne change pas
        
        
        // *Condition : debut[$i] >= debut[$last] and fin[$i] <= fin[$last]
        // |-------------------------------|    $last
        // |-------------------------------|    $i
        // |--------------------------|         $i
        //      |--------------------------|    $i
        //      |---------------------|         $i
        
        if($result[$i]['perso_id'] == $result[$last]['perso_id'] and $result[$i]['debut'] < $result[$last]['fin']){
          if($result[$i]['debut'] >= $result[$last]['debut'] and $result[$i]['fin'] <= $result[$last]['fin']){
            $cles_a_supprimer[] = $i;
          } elseif($result[$i]['debut'] == $result[$last]['debut'] and $result[$i]['fin'] > $result[$last]['fin']){
            $cles_a_supprimer[] = $last;
            $last = $i;
          } elseif($result[$i]['debut'] > $result[$last]['debut'] and $result[$i]['fin'] > $result[$last]['fin']){
            $result[$last]['fin']=$result[$i]['debut'];
            $last = $i;
          } else {
            $last = $i;
          }
        } else {
          $last = $i;
        }
      }
      foreach($cles_a_supprimer as $elem){
        unset($result[$elem]);
      }
    }
    
    if($result){
      $this->elements=$result;
    }
  }

  public function fetchById($id){
    $db=new db();
    $db->selectInnerJoin(array("absences","perso_id"),array("personnel","id"),
      array("id","debut","fin","motif","motif_autre","commentaires","valide_n1","validation_n1","pj1","pj2","so","demande","groupe","ical_key","cal_name","rrule","uid",
      array("name"=>"valide","as"=>"valide_n2"),array("name"=>"validation","as"=>"validation_n2")),
      array("nom","prenom","sites",array("name"=>"id","as"=>"perso_id"),"mail","mails_responsables"),
      array("id"=>$id));

    if($db->result){
      $result=$db->result[0];
      $result['mails_responsables']=explode(";",html_entity_decode($result['mails_responsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
      
      // Créé un tableau $agents qui sera placé dans $this->elements['agents']
      // Ce tableau contient un tableau par agent avec les informations le concernant (nom, prenom, mail, etc.)
      // En cas d'absence enregistrée pour plusieurs agents, il sera complété avec les informations des autres agents
      $sites = json_decode(html_entity_decode($result['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
      $agents=array(array("perso_id"=>$result['perso_id'], "nom"=>$result['nom'], "prenom"=>$result['prenom'], "sites"=>$sites, "mail"=>$result['mail'], "mails_responsables"=>$result['mails_responsables'], "absence_id"=>$id));
      $perso_ids=array($result['perso_id']);

      // Absence concernant plusieurs agents
      // Complète le tableau $agents
      if($result['groupe']){
	$groupe=$result['groupe'];
        $debut=$result['debut'];
        $fin=$result['fin'];
	$agents=array();

	// Recherche les absences enregistrées sous le même groupe et les infos des agents concernés
	$db=new db();
	$db->selectInnerJoin(array("absences","perso_id"),array("personnel","id"),
	  array("id"),
	  array("nom","prenom","sites",array("name"=>"id","as"=>"perso_id"),"mail","mails_responsables"),
          array("groupe"=>$groupe, "debut"=>$debut, "fin"=>$fin),
	  array(),
	  "order by nom, prenom");
	
	// Complète le tableau $agents
	if($db->result){
	  foreach($db->result as $elem){
	    $elem['mails_responsables']=explode(";",html_entity_decode($elem['mails_responsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	    $sites = json_decode(html_entity_decode($elem['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	    $agent=array("perso_id"=>$elem['perso_id'], "nom"=>$elem['nom'], "prenom"=>$elem['prenom'], "sites"=>$sites, "mail"=>$elem['mail'], "mails_responsables"=>$elem['mails_responsables'], "absence_id"=>$elem['id']);
	    if(!in_array($agent,$agents)){
	      $agents[]=$agent;
	      $perso_ids[]=$elem['perso_id'];
	    }
	  }
	}
      }

      // Le champ commentaires peut comporter des <br/> ou équivalents HTML lorsqu'il est importé depuis un fichier ICS. On les remplace par \n
      $result['commentaires'] = str_replace(array('<br/>','&lt;br/&gt;'), "\n", $result['commentaires']);
      $result['agents']=$agents;
      $result['perso_ids']=$perso_ids;
      $this->elements=$result;
    }
  }


  function getResponsables($debut=null,$fin=null,$perso_id){
    $responsables=array();
    $droitsAbsences=array();
    //	Si plusieurs sites et agents autorisés à travailler sur plusieurs sites, vérifions dans l'emploi du temps quels sont les sites concernés par l'absence
    if($GLOBALS['config']['Multisites-nombre']>1){
      $db=new db();
      $db->select("personnel","temps","id='$perso_id'");
      $temps=json_decode(html_entity_decode($db->result[0]['temps'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
      $date=$debut;
      while($date<=$fin){
	// Emploi du temps si module planningHebdo activé
	if($GLOBALS['config']['PlanningHebdo']){
          $version = $GLOBALS['version'];
          include_once __DIR__."/../planningHebdo/class.planningHebdo.php";
	  $p=new planningHebdo();
	  $p->perso_id=$perso_id;
	  $p->debut=$date;
	  $p->fin=$date;
	  $p->valide=true;
	  $p->fetch();

	  if(empty($p->elements)){
	    $temps=array();
	  }
	  else{  
	    $temps=$p->elements[0]['temps'];
	  }
	}
	// Vérifions le numéro de la semaine de façon à contrôler le bon planning de présence hebdomadaire
	$d=new datePl($date);
	$jour=$d->position?$d->position:7;
	$semaine=$d->semaine3;
	// Récupération du numéro du site concerné par la date courante
	$j=$jour-1+($semaine*7)-7;
	$site=null;
	if(is_array($temps)){
	  if(array_key_exists($j,$temps) and array_key_exists(4,$temps[$j])){
	    $site=$temps[$j][4];
	  }
	}
	// Ajout du numéro du droit correspondant à la gestion des absences de ce site
	if(!in_array("20".$site,$droitsAbsences) and $site){
	  $droitsAbsences[]="20".$site;
	}
	$date=date("Y-m-d",strtotime("+1 day",strtotime($date)));
      }
      // Si les jours d'absences ne concernent aucun site, on ajoute les responsables des 2 sites par sécurité
      if(empty($droitsAbsences)){
	for($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++){
	  $droitsAbsences[]=200+$i;
	}
      }
    }
    // Si un seul site, le droit de gestion des absences est 1
    else{
      $droitsAbsences[]=1;
    }

    $db=new db();
    $db->select("personnel",null,"supprime='0'");
    foreach($db->result as $elem){
      $d=json_decode(html_entity_decode($elem['droits'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
      foreach($droitsAbsences as $elem2){
	if(is_array($d) and in_array($elem2,$d) and !in_array($elem,$responsables)){
	  $responsables[]=$elem;
	}
      }
    }
    $this->responsables=$responsables;
  }

  public function getRecipients($validation,$responsables,$mail,$mails_responsables){
    /*
    Retourne la liste des destinataires des notifications en fonction du niveau de validation.
    $validation = niveau de validation (int) :
      1 : enregistrement d'une nouvelle absences
      2 : modification d'une absence sans validation ou suppression
      3 : validation N1
      4 : validation N2
    $responsables : listes des agents (array) ayant le droit de gérer les absences
    $mail : mail de l'agent concerné par l'absence
    $mails_responsables : mails de ses responsables (tableau)
    */

    $categories=$GLOBALS['config']["Absences-notifications{$validation}"];
    $categories=json_decode(html_entity_decode(stripslashes($categories),ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
    /*
    $categories : Catégories de personnes à qui les notifications doivent être envoyées
      tableau sérialisé issu de la config. : champ Absences-notifications, Absences-notifications2, 
      Absences-notifications3, Absences-notifications4, en fonction du niveau de validation ($validation)
      Valeurs du tableau : 
	0 : agents ayant le droits de gérer les absences
	1 : responsables directs (mails enregistrés dans la fiche des agents)
	2 : cellule planning (mails enregistrés dans la config.)
	3 : l'agent
    */

    // recipients : liste des mails qui sera retournée
    $recipients=array();

    // Agents ayant le droits de gérer les absences
    if(in_array(0,$categories)){
      foreach($responsables as $elem){
	if(!in_array(trim(html_entity_decode($elem['mail'],ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	  $recipients[]=trim(html_entity_decode($elem['mail'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	}
      }
    }

    // Responsables directs
    if(in_array(1,$categories)){
      if(is_array($mails_responsables)){
	foreach($mails_responsables as $elem){
	  if(!in_array(trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	    $recipients[]=trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	  }
	}
      }
    }

    // Cellule planning
    if(in_array(2,$categories)){
      $mailsCellule=explode(";",trim($GLOBALS['config']['Mail-Planning']));
      if(is_array($mailsCellule)){
	foreach($mailsCellule as $elem){
	  if(!in_array(trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	    $recipients[]=trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	  }
	}
      }
    }

    // L'agent
    if(in_array(3,$categories)){
      if(!in_array(trim(html_entity_decode($mail,ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	$recipients[]=trim(html_entity_decode($mail,ENT_QUOTES|ENT_IGNORE,"UTF-8"));
      }
    }

    $this->recipients=$recipients;
  }


  /**
   * @function ics_add_event
   * Enregistre un événement dans le fichier ICS "Planning Biblio" de l'agent sélectionné
   * @params : tous les éléments d'une absence : date et heure de début et de fin, motif, commentaires, validation, ID de l'agent, règle de récurrence (rrule)
   */
  public function ics_add_event(){

    // Initilisation des variables, adaptation des valeurs
    $perso_id = $this->perso_id;
    $folder = sys_get_temp_dir();
    $file = "$folder/PBCalendar-$perso_id.ics";
    $tzid = date_default_timezone_get();
    $dtstart = preg_replace('/(\d+)\/(\d+)\/(\d+)/','$3$2$1',$this->debut).'T';
    $dtstart .= preg_replace('/(\d+):(\d+):(\d+)/','$1$2$3',$this->hre_debut);
    $dtend = preg_replace('/(\d+)\/(\d+)\/(\d+)/','$3$2$1',$this->fin).'T';
    $dtend .= preg_replace('/(\d+):(\d+):(\d+)/','$1$2$3',$this->hre_fin);
    $dtstamp = !empty($this->dtstamp) ? $this->dtstamp : gmdate('Ymd\THis\Z');
    $summary = $this->motif_autre ? html_entity_decode($this->motif_autre, ENT_QUOTES|ENT_IGNORE, 'UTF-8') : html_entity_decode($this->motif, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
    $cal_name = "PlanningBiblio-Absences-$perso_id-$dtstamp";
    $uid = !empty($this->uid) ? $this->uid : $dtstart."_".$dtstamp;
    $status = $this->valide_n2 > 0 ? 'CONFIRMED' : 'TENTATIVE';

    // Description : en supprime les entités HTML et remplace les saut de lignes par des <br/> pour facilité le traitement des saut de lignes à l'affichage et lors des remplacements
    $description = html_entity_decode($this->commentaires, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
    $description = str_replace("\n", "<br/>", $description);

    // Gestion des groupes et des validations, utilisation du champ CATEGORIES
    $categories = array();
    if($this->groupe){
      $categories[] = "PBGroup=".$this->groupe;
    }
    if($this->valide_n1){
      $categories[] = "PBValideN1=".$this->valide_n1;
    }
    if($this->validation_n1){
      $categories[] = "PBValidationN1=".$this->validation_n1;
    }
    if($this->valide_n2){
      $categories[] = "PBValideN2=".$this->valide_n2;
    }
    if($this->validation_n2){
      $categories[] = "PBValidationN2=".$this->validation_n2;
    }
    $categories = implode(';', $categories);

    // On créé l'entête du fichier ICS
    $ics_content = "BEGIN:VCALENDAR\n";
    $ics_content .= "PRODID:-//Planning Biblio//Planning Biblio 2.7.04//FR\n";
    $ics_content .= "VERSION:2.7.04\n";
    $ics_content .= "CALSCALE:GREGORIAN\n";
    $ics_content .= "METHOD:PUBLISH\n";
    $ics_content .= "X-WR-CALNAME:$cal_name\n";
    $ics_content .= "X-WR-TIMEZONE:$tzid\n";
    $ics_content .= "BEGIN:VTIMEZONE\n";
    $ics_content .= "TZID:$tzid\n";
    $ics_content .= "X-LIC-LOCATION:$tzid\n";
    $ics_content .= "END:VTIMEZONE\n";

    // On créé un événement ICS
    $ics_content .= "BEGIN:VEVENT\n";
    $ics_content .= "UID:$uid\n";
    $ics_content .= "DTSTART;TZID=$tzid:$dtstart\n";
    $ics_content .= "DTEND;TZID=$tzid:$dtend\n";
    $ics_content .= "DTSTAMP:$dtstamp\n";
    $ics_content .= "CREATED:$dtstamp\n";
    $ics_content .= "LAST-MODIFIED:$dtstamp\n";
    $ics_content .= "LOCATION:\n";
    $ics_content .= "STATUS:$status\n";
    $ics_content .= "SUMMARY:$summary\n";
    $ics_content .= "DESCRIPTION:$description\n";
    $ics_content .= "CATEGORIES:$categories\n";
    $ics_content .= "TRANSP:OPAQUE\n";
    $ics_content .= "RRULE:{$this->rrule}\n";
    $ics_content .= "END:VEVENT\n";
    $ics_content .= "END:VCALENDAR\n";

    // Précise si la fin de la récurrence existe pour continuer à la traiter à l'avenir si elle n'est pas renseignée
    $end = (strpos($this->rrule, 'UNTIL=') or strpos($this->rrule, 'COUNT=')) ? 1 : 0;

    // On enregistre les infos dans la base de données
    $db = new db();
    $db->CSRFToken = $this->CSRFToken;
    $db->insert('absences_recurrentes', array('uid' => $uid, 'perso_id' => $perso_id, 'event' => $ics_content, 'end' => $end));

    logs("Agent #$perso_id : Importation du fichier $file", "ICS", $this->CSRFToken);

    // On ecrit le fichier
    file_put_contents($file, $ics_content);

    $ics=new CJICS();
    $ics->src = $file;
    $ics->perso_id = $perso_id;
    $ics->pattern = '[SUMMARY]';
    $ics->status = 'All';
    $ics->table ="absences";
    $ics->logs = true;
    $ics->CSRFToken = $this->CSRFToken;
    $ics->updateTable();

    // On supprime le fichier
    unlink($file);
  }


  /** @function ics_add_exdate($date);
   * @param string $this->uid : UID d'un événement ICS "Planning Biblio" (ex: 20171110120000_20171110115523Z)
   * @param int $this->perso_id : ID de l'agent
   * @param string $date : date et heure de l'exception au format ICS (ex: 20171110T120000)
   * @desc : ajoute une exception sur un événement ICS "Planning Biblio"
   */
  public function ics_add_exdate($date){

    $this->ics_get_event();
    $ics_event = $this->elements;
    $perso_id = $this->perso_id;
    
    if($ics_event){
      // On modifie la date LAST-MODIFIED
      $ics_event = preg_replace("/LAST-MODIFIED:.[^\n]*\n/", "LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n", $ics_event);

      // On modifie l'événement en ajoutant une exception
      if(strpos($ics_event, 'EXDATE')){
        $ics_event = preg_replace("/(EXDATE.[^\n]*)\n/", "$1,$date\n", $ics_event);
      } else {
        $exdate = "EXDATE;TZID=".date_default_timezone_get().":$date";
        $ics_event = str_replace("END:VEVENT", "$exdate\nEND:VEVENT", $ics_event);
      }

      $folder = sys_get_temp_dir();
      $file = "$folder/PBCalendar-$perso_id.ics";

      file_put_contents($file, $ics_event);

      // On met à jour l'événement dans la table absences_recurrentes
      $db = new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->update('absences_recurrentes', array('event' => $ics_event, 'last_update' => 'SYSDATE'), array('uid' => $this->uid, 'perso_id' => $perso_id));

      // On actualise la base de données à partir de l'événement ICS modifié
      $ics=new CJICS();
      $ics->src = $file;
      $ics->perso_id = $perso_id;
      $ics->pattern = '[SUMMARY]';
      $ics->status = 'All';
      $ics->table ="absences";
      $ics->logs = true;
      $ics->CSRFToken = $this->CSRFToken;
      $ics->updateTable();

      // On supprime le fichier
      unlink($file);
    }
  }


  /** @function ics_delete_event
   * @param string $this->uid : UID d'un événement ICS "Planning Biblio" (ex: 20171110120000_20171110115523Z)
   * @param int $this->perso_id : ID de l'agent
   * @desc : supprime un événement ICS "Planning Biblio"
   * @note : Les lignes UID des fichiers ICS doivent directement suivre les lignes BEGIN:VEVENT
   */
  public function ics_delete_event(){
    $perso_id = $this->perso_id;
    $uid = $this->uid;

    // Suppression de l'événement dans la base de données (table absences)
    $db = new db();
    $db->CSRFToken = $this->CSRFToken;
    $db->delete('absences', array('perso_id'=> $perso_id, 'uid'=> $uid, 'cal_name' => "LIKEPlanningBiblio-Absences-$perso_id%"));

    // Suppression de l'événement dans la base de données (table absences_recurrentes)
    $db = new db();
    $db->CSRFToken = $this->CSRFToken;
    $db->delete('absences_recurrentes', array('perso_id'=> $perso_id, 'uid'=> $uid));
  }


  /** @function ics_get_event
   * @param string $this->uid : UID d'un événement ICS "Planning Biblio" (ex: 20171110120000_20171110115523Z)
   * @param int $this->perso_id : ID de l'agent
   * @return array $this->elements : tableau PHP contenant l'événement, un élément par ligne du fichier ICS
   * @return $this->elements = null si le fichier ICS n'a pas été trouvé
   * @note : Les lignes UID des fichiers ICS doivent directement suivre les lignes BEGIN:VEVENT
   */
  public function ics_get_event(){
  
    // Récupère l'événement depuis la base de données
    $where = array('uid' => $this->uid);
    if(!empty($this->perso_id)){
      $where['perso_id'] = $this->perso_id;
    }

    $db = new db();
    $db->select2('absences_recurrentes', 'event', $where);
    
    if($db->result){
      $this->elements = $db->result[0]['event'];
    }
  }


  /** @function ics_update_event();
   * @param string $this->uid : UID d'un événement ICS "Planning Biblio" (ex: 20171110120000_20171110115523Z)
   * @param int $this->perso_ids : IDs des agents
   * @params : tous les éléments d'une absence : date et heure de début et de fin (format FR JJ/MM/YYYY et hh:mm:ss), motif, commentaires, validation, ID de l'agent, règle de récurrence (rrule)
   * @desc : modifie un événement ICS "Planning Biblio"
   */
  public function ics_update_event(){

    // TODO : gérer la suppression des agents

    // Recherche de l'événement pour récupèrer la date de départ pour la création des événements des agents ajoutés.
    $a = new absences();
    $a->uid = $this->uid;
    $a->ics_get_event();
    $event = $a->elements;

    if(empty($event)){
      return false;
    }

    // récupération de la date de début de la série
    preg_match('/DTSTART.*:(\d*)T\d*\n/', $event, $matches);
    $debut = date('d/m/Y', strtotime($matches[1]));
    preg_match('/DTEND.*:(\d*)T\d*\n/', $event, $matches);
    $fin = date('d/m/Y', strtotime($matches[1]));

    // Pour chaque agent
    foreach($this->perso_ids as $perso_id){
      $a = new absences();
      $a->perso_id = $perso_id;
      $a->uid = $this->uid;
      $a->ics_get_event();
      $ics_event = $a->elements;

      // Pour chaque agent, si l'agent faisait déjà partie de l'événement, on modifie les infos
      if($ics_event){
        // On actualise les infos

        // TODO : pour le moment, on ne touche pas aux dates et aux RRULEs : A voir ensuite
        // $dtstart = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $this->debut);  // faux car la date de début de la série serait remplacée par la date de début de l'occurence choisie

        // Mise à jour des heures de début
        $start = str_replace(':', null, $this->hre_debut);
        $ics_event = preg_replace("/(DTSTART.[^:]*):(\d+)T\d+\n/", "$1:$2T$start\n", $ics_event);

        // Mise à jour des heures de fin
        $end = str_replace(':', null, $this->hre_fin);
        $ics_event = preg_replace("/(DTEND.[^:]*):(\d+)T\d+\n/", "$1:$2T$end\n", $ics_event);

        // Mise à jour de LAST-MODIFIED
        $ics_event = preg_replace("/LAST-MODIFIED:.*\n/", "LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n", $ics_event);

        // Mise à jour de STATUS
        $status = $this->valide_n2 > 0 ? 'CONFIRMED' : 'TENTATIVE';
        $ics_event = preg_replace("/STATUS:.*\n/", "STATUS:$status\n", $ics_event);

        // Mise à jour de CATEGORIES (validation et groupe)
        // Exemple : PBGroup=1510848337-470;PBValideN1=1;PBValidationN1=2017-11-16 17:05:37;PBValidationN2=0000-00-00 00:00:00
        $tmp = array();
        if($this->groupe){        $tmp[] = "PBGroup={$this->groupe}"; }
        if($this->valide_n1){     $tmp[] = "PBValideN1={$this->valide_n1}"; }
        if($this->validation_n1){ $tmp[] = "PBValidationN1={$this->validation_n1}"; }
        if($this->valide_n2){     $tmp[] = "PBValideN2={$this->valide_n2}"; }
        if($this->validation_n2){ $tmp[] = "PBValidationN2={$this->validation_n2}"; }

        if(!empty($tmp)){
          $categories = 'CATEGORIES:'.implode(';', $tmp);
          $categories = str_replace("\n", null, $categories)."\n";
        }

        if(strpos($ics_event, 'CATEGORIES:')){
          $ics_event = preg_replace("/CATEGORIES:.*\n/", "CATEGORIES:$categories\n", $ics_event);
        } else {
          $ics_event = str_replace('END:VEVENT',"CATEGORIES:$categories\nEND:VEVENT", $ics_event);
        }

        // Mise à jour de SUMMARY
        $summary = $this->motif == 'Autre' ? $this->motif_autre : $this->motif;
        $summary = html_entity_decode($summary, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $ics_event = preg_replace("/SUMMARY:.*\n/", "SUMMARY:$summary\n", $ics_event);

        // Mise à jour de DESCRIPTION
        // Description : on supprime les entités HTML et remplace les sauts de ligne par des <br/> pour faciliter le traitement des sauts de ligne à l'affichage et lors des remplacements
        $description = html_entity_decode($this->commentaires, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $description = str_replace("\n", "<br/>", $description);
        $ics_event = preg_replace("/DESCRIPTION:.*\n/", "DESCRIPTION:$description\n", $ics_event);

        // Modification de RRULE
        // TODO : Adapter la modification du RRULE si la date de début change
        $ics_event = preg_replace("/RRULE:.*\n/", "RRULE:{$this->rrule}\n", $ics_event);

        // Précise si la fin de la récurrence existe pour continuer à la traiter à l'avenir si elle n'est pas renseignée
        $end = (strpos($this->rrule, 'UNTIL=') or strpos($this->rrule, 'COUNT=')) ? 1 : 0;

        // On met à jour l'événement dans la table absences_recurrentes
        $db = new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update('absences_recurrentes', array('event' => $ics_event, 'end' => $end, 'last_update' => 'SYSDATE'), array('uid' => $this->uid, 'perso_id' => $perso_id ));

        // Ecriture dans le fichier
        $folder = sys_get_temp_dir();
        $file = "$folder/PBCalendar-$perso_id.ics";

        file_put_contents($file, $ics_event);

        // On actualise la base de données à partir de l'événement ICS modifié
        $ics=new CJICS();
        $ics->src = $file;
        $ics->perso_id = $perso_id;
        $ics->pattern = '[SUMMARY]';
        $ics->status = 'All';
        $ics->table ="absences";
        $ics->logs = true;
        $ics->CSRFToken = $this->CSRFToken;
        $ics->updateTable();

        unlink($file);
      }

      // Pour chaque agent ajouté (ne faisant pas partie de l'événement avant la modification), on créé l'événement
      else {
        // Création du fichier ICS
        $a = new absences();
        $a->CSRFToken = $this->CSRFToken;
        $a->perso_id = $perso_id;
        $a->commentaires = $this->commentaires;
        $a->debut = $debut;
        $a->fin = $fin;
        $a->hre_debut = $this->hre_debut;
        $a->hre_fin = $this->hre_fin;
        $a->groupe = $this->groupe;
        $a->motif = $this->motif;
        $a->motif_autre = $this->motif_autre;
        $a->rrule = $this->rrule;
        $a->valide_n1 = $this->valide_n1;
        $a->valide_n2 = $this->valide_n2;
        $a->validation_n1 = $this->validation_n1;
        $a->validation_n2 = $this->validation_n2;
        $a->uid = $this->uid;
        $a->ics_add_event();
      }

    }
  }


  /** @function ics_update_table
   * @desc : Recherche une fois par jour si des occurences liées à des absences récurrentes sans date de fin doivent être ajoutées dans la table absences
   * @note : la méthode CJICS::updateTable utilisée pour alimenter la table absence n'ajoute que les événements des 2 prochaines année, c'est pourquoi nous devons la réexecuter régulièrement
   */
  function ics_update_table(){
    $db = new db();
    $db->select2('absences_recurrentes', null, array('end' => '0' , 'last_check' => "< CURDATE"));
    if($db->result){
      foreach($db->result as $elem){
        $perso_id = $elem['perso_id'];
        $uid = $elem['uid'];
        $event = $elem['event'];

        $folder = sys_get_temp_dir();
        $file = "$folder/PBCalendar-$perso_id.ics";

        file_put_contents($file, $event);

        // On actualise la base de données à partir du fichier ICS modifié
        $ics=new CJICS();
        $ics->src = $file;
        $ics->perso_id = $perso_id;
        $ics->pattern = '[SUMMARY]';
        $ics->status = 'All';
        $ics->table ="absences";
        $ics->logs = true;
        $ics->CSRFToken = $this->CSRFToken;
        $ics->updateTable();

        // On supprime le fichier
        unlink($file);
      }

      // On met à jour le champ last_check de façon à ne pas relancer l'opération dans la journée
      $db = new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->update('absences_recurrentes', array('last_check' => "SYSDATE"), array('end' => '0'));
    }
  }


  /** @function ics_update_until($datetime);
   * @param string $this->uid : UID d'un événement ICS "Planning Biblio" (ex: 20171110120000_20171110115523Z)
   * @param int $this->perso_id : ID de l'agent
   * @param string $datetime : date et heure de fin de série, format ICS, timezone GMT (20171110T120000Z)
   * @desc : modifie la date de fin de série d'un événement ICS "Planning Biblio"
   */
  public function ics_update_until($datetime){

    $this->ics_get_event();
    $ics_event = $this->elements;
    $perso_id = $this->perso_id;

    if($ics_event){

      // Mise à jour de LAST-MODIFIED
      $ics_event = preg_replace("/LAST-MODIFIED:.*\n/", "LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n", $ics_event);

      // On modifie ou ajoute une date de fin à RRULE
      preg_match("/\nRRULE:(.*)\n/", $ics_event, $matches);
      $rrule = substr($matches[0],7);
      $rrule = str_replace("\n", null, $rrule);

      if(strpos($rrule, 'UNTIL')) {
        $rrule = preg_replace("/UNTIL=\d+T\d+Z/", "UNTIL=$datetime", $rrule);
      } elseif(strpos($rrule, 'COUNT')) {
        $rrule = preg_replace('/COUNT=\d+/', "UNTIL=$datetime", $rrule);
      } else {
        $rrule = preg_replace("/$/", ";UNTIL=$datetime", $rrule);
      }
      $ics_event = preg_replace("/\nRRULE:.*\n/", "\nRRULE:$rrule\n", $ics_event);

      // Ecriture dans le fichier
      $folder = sys_get_temp_dir();
      $file = "$folder/PBCalendar-$perso_id.ics";

      file_put_contents($file, $ics_event);
      
      $db = new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->update('absences_recurrentes', array('event' => $ics_event, 'end' => '1', 'last_update' => 'SYSDATE'), array('uid' => $this->uid, 'perso_id' => $perso_id));

      // On actualise la base de données à partir du fichier ICS modifié
      $ics=new CJICS();
      $ics->src = $file;
      $ics->perso_id = $perso_id;
      $ics->pattern = '[SUMMARY]';
      $ics->status = 'All';
      $ics->table ="absences";
      $ics->logs = true;
      $ics->CSRFToken = $this->CSRFToken;
      $ics->updateTable();
      
      // On supprime le fichier
      unlink($file);
    }
  }

  /**
  * infoPlannings
  * Retourne la liste des plannings concernés (dates, horaires sites et postes) (@param $this->message @string)
  * @param $this->debut @string
  * @param $this->fin @string
  * @param $this->perso_id @int
  * TODO : si besoin, cette fonction peut être complétée de façon à retourner les infos sous forme de tableaux
  * (dates des plannings concernés, validés ou non, postes et sites concernés)
  * TODO : voir s'il faut faire une synthèse pour alléger le mail si de nombreux plannings sont concernés
  */
  function infoPlannings(){
    $version="absences";
    require_once "postes/class.postes.php";
  
    $debut=dateSQL($this->debut);
    $fin=dateSQL($this->fin);
    $perso_ids=implode(",",$this->perso_ids);

    $dateDebut=substr($debut,0,10);
    $dateFin=substr($fin,0,10);
    
    $heureDebut=substr($debut,11);
    $heureFin=substr($fin,11);

    // Recherche des plages de SP concernées pour ajouter cette information dans le mail.
    // Recherche des plannings validés
    $plannings_valides=array();
    $db=new db();
    $db->select2("pl_poste_verrou","date",array("date"=>"BETWEEN $dateDebut AND $dateFin","verrou2"=>"1"));
    if($db->result){
      foreach($db->result as $elem){
	$plannings_valides[]=$elem['date'];
      }
    }

    sort($plannings_valides);
    $dates=implode($plannings_valides,",");

    // nom des postes
    $p=new postes();
    $p->fetch();
    $postes=$p->elements;
    
    // Nom des sites
    $sites=array(1=>null);
    if($GLOBALS['config']['Multisites-nombre']>1){
      for($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++){
	$sites[$i]=$GLOBALS['config']["Multisites-site$i"];
      }
    }

    // Recherche des plannings dans lequel apparaît l'agent
    $plannings=array();
    $db=new db();
    $db->select2("pl_poste",null,array("date"=>"BETWEEN $dateDebut AND $dateFin","perso_id"=>"IN $perso_ids"),"ORDER BY date,debut,fin");
    if($db->result){
      foreach($db->result as $elem){
	// On exclu les créneaux horaires qui sont en dehors de l'absences
	if($elem['date']==$dateDebut and $elem['fin']<=$heureDebut){
	  continue;
	}
	if($elem['date']==$dateFin and $elem['debut']>=$heureFin){
	  continue;
	}

	$elem['valide']=in_array($elem['date'],$plannings_valides)?" (Valid&eacute;)":null;
	$elem['date']=dateFr($elem['date']);
	$elem['debut']=heure2($elem['debut']);
	$elem['fin']=heure2($elem['fin']);
	$elem['site']=$sites[$elem['site']];
	$elem['poste']=$postes[$elem['poste']]['nom'];
	$plannings[]=$elem;
      }
    }
    
    // Création du message
    // Par défaut, message = aucun planning n'est concerné
    $message="<p>Aucun planning n&apos;est affect&eacute; par cette absence.</p>";
    
    // Si des plannings sont concernés
    if(!empty($plannings)){
      // Fusionne les plages horaires si sur le même poste sur des plages successives
      $tmp=array();
      $j=0;
      for($i=0; $i<count($plannings);$i++){
	if($i==0){
	  $tmp[$j]=$plannings[$i];
	}elseif($plannings[$i]['site']==$tmp[$j]['site'] and $plannings[$i]['poste']==$tmp[$j]['poste']
	    and $plannings[$i]['debut']==$tmp[$j]['fin']){
	  $tmp[$j]['fin']=$plannings[$i]['fin'];
	}else{
	  $j++;
	  $tmp[$j]=$plannings[$i];
	}
      }
      $plannings=$tmp;
      
      // Rédaction du message
      $message="<p><strong>Les plannings suivants sont affect&eacute;s par cette absence :</strong><ul>\n";
      $lastDate=null;
      foreach($plannings as $elem){
	if($elem['date']!=$lastDate and $lastDate!=null){
	  $message.="</ul></li>\n";
	}
	if($elem['date']!=$lastDate){
	  $message.="<li><strong>{$elem['date']}{$elem['valide']}</strong><ul>\n";
	}
	$message.="<li>{$elem['debut']}-{$elem['fin']} {$elem['site']} {$elem['poste']}</li>\n";
	$lastDate=$elem['date'];
      }
      $message.="</ul></li></ul></p>\n";
    }
    
    $this->message=$message;
  }

  function piecesJustif($id,$pj, $checked){
    $db=new db();
    $db->CSRFToken = $this->CSRFToken;
    $db->update("absences",array($pj => $checked),array("id"=>$id));
  }


  public function update_time(){
    $db=new db();
    $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['config']['dbprefix']}absences';");
    return $db->result[0]['Update_time'];
  }

}
?>