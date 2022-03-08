<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/class.absences.php
Création : mai 2011
Dernière modification : 30 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe absences : contient les fonctions de recherches des absences

Page appelée par les autres pages du dossier absences

TODO : Il serait intéressant de sortir de la boucle la gestion des notifications de la méthode add() comme ce qui a été fait pour les modifications (modif2.php).
TODO : Si modification des notifications : adapter le message (lister tous les agents), adapter les variables fournies à getRecipients2, refaire une boucle pour getRecipients ou adapter getRecipients
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
$version = $GLOBALS['version'] ?? null;

if (!isset($version) and php_sapi_name() != 'cli') {
    require_once __DIR__."/../include/accessDenied.php";
}

require_once __DIR__."/../ics/class.ics.php";
require_once __DIR__."/../personnel/class.personnel.php";

use App\Model\Agent;
use App\Model\AbsenceReason;
use App\Model\AbsenceDocument;
use App\PlanningBiblio\WorkingHours;
use App\PlanningBiblio\ClosingDay;


class absences
{
    public $agents_supprimes=array(0);
    public $CSRFToken=null;
    public $cal_name;
    public $commentaires=null;
    public $debut=null;
    public $dtstamp=null;
    public $edt=array();
    public $elements=array();
    public $error=false;
    public $exdate = null;
    public $fin=null;
    public $groupe=null;
    public $heures=0;
    public $heures2=null;
    public $hre_debut = null;
    public $hre_fin = null;
    public $id = null;
    public $ignoreFermeture=false;
    public $last_modified = null;
    public $minutes=0;
    public $motif = null;
    public $motif_autre = null;
    public $perso_id=null;
    public $perso_ids=array();
    public $recipients=array();
    public $rrule = null;
    public $teleworking = true;
    public $validation_n1 = null;
    public $validation_n2 = null;
    public $valide=false;
    public $rejected = true;
    public $valide_n1 = null;
    public $valide_n2 = null;
    public $uid=null;
    public $unique=false;
    public $update_db = false;

    public function __construct()
    {
    }

  
    /** @function add()
     * Enregistre une nouvelle absence dans la base de données, créé les fichiers ICS pour les absences récurrentes (appel de la methode ics_add_event), envoie les notifications
     * @params : tous les éléments nécessaires à la création d'une absence
     * @return : message d'erreur ou de succès de l'enregistrement et de l'envoi des notifications
     */
    public function add()
    {
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

        $em = $GLOBALS['entityManager'];

        // Validation
        // Validation, valeurs par défaut
        $valide_n1 = 0;
        $valide_n2 = 0;
        $validation_n1 = "0000-00-00 00:00:00";
        $validation_n2 = "0000-00-00 00:00:00";
        $validationText = "Demand&eacute;e";

        // Si le workflow est désactivé, absence directement validée
        if (!$GLOBALS['config']['Absences-validation']) {
            $valide_n2 = 1;
            $validation_n2 = date("Y-m-d H:i:s");
            $validationText = null;
        }
        // Si workflow, validation en fonction de $this->valide
        else {
            switch ($this->valide) {
                case 1:
                    $valide_n2 = $_SESSION['login_id'];
                    $validation_n2 = date("Y-m-d H:i:s");
                    $validationText = "Valid&eacute;e";
                    break;

                case -1:
                    $valide_n2 = $_SESSION['login_id']*-1;
                    $validation_n2 = date("Y-m-d H:i:s");
                    $validationText = "Refus&eacute;e";
                    break;

                case 2:
                    $valide_n1 = $_SESSION['login_id'];
                    $validation_n1 = date("Y-m-d H:i:s");
                    $validationText = "Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
                    break;

                case -2:
                    $valide_n1 = $_SESSION['login_id']*-1;
                    $validation_n1 = date("Y-m-d H:i:s");
                    $validationText = "Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
                    break;
            }
        }

        // Choix des destinataires des notifications selon le degré de validation
        $notifications = 1;
        if ($GLOBALS['config']['Absences-validation'] and $valide_n1 != 0) {
            $notifications = 3;
        } elseif ($GLOBALS['config']['Absences-validation'] and $valide_n2 != 0) {
            $notifications=4;
        }

        $workflow = 'A';
        $reason = $em->getRepository(AbsenceReason::class)->findoneBy(['valeur' => $motif]);
        if ($reason) {
            $workflow = $reason->notification_workflow();
            if (!isset($workflow)) {
                $workflow = 'A';
            }
        }
        $notifications = "-$workflow$notifications";

        // Formatage des dates/heures de début/fin pour les requêtes SQL
        $debut_sql = $debutSQL.' '.$hre_debut;
        $fin_sql = $finSQL.' '.$hre_fin;

        // Si erreur d'envoi de mail, affichage de l'erreur (Initialisation des variables)
        $msg2=null;
        $msg2_type=null;

        // ID du groupe (permet de regrouper les informations pour affichage en une seule ligne et modification du groupe)
        $groupe = (count($perso_ids) > 1) ? time().'-'.rand(100, 999) : null;

        // On définie le dtstamp avant la boucle, sinon il différe selon les agents, ce qui est problématique pour retrouver les événéments des membres d'un groupe pour les modifications car le DTSTAMP est intégré dans l'UID
        $dtstamp = gmdate('Ymd\THis\Z');

        $agents = $em->getRepository(Agent::class)->findById($perso_ids);

        // Pour chaque agents
        foreach ($agents as $agent) {
            // Enregistrement des récurrences
            // Les événements récurrents sont enregistrés dans un fichier ICS puis importés dans la base de données
            // La méthode absences::ics_add_event se charge de créer le fichier et d'enregistrer les infos dans la base de données
            if ($this->rrule) {
                // Création du fichier ICS
                $a = new absences();
                $a->CSRFToken = $this->CSRFToken;
                $a->dtstamp = $dtstamp;
                $a->exdate = $this->exdate;
                $a->perso_id = $agent->id();
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
                $a->id = $this->id;
                $a->ics_add_event();

            // Les événements sans récurrence sont enregistrés directement dans la base de données
            } else {
                // Ajout de l'absence dans la table 'absence'
                $insert = array(
                    "perso_id" => $agent->id(),
                    "debut" => $debut_sql,
                    "fin" => $fin_sql,
                    "motif" => $motif,
                    "motif_autre" => $motif_autre,
                    "commentaires" => $commentaires,
                    "demande" => date("Y-m-d H:i:s"),
                    "pj1" => $this->pj1,
                    "pj2" => $this->pj2,
                    "so" => $this->so,
                    "groupe" => $groupe
                );

                if ($valide_n1 != 0) {
                    $insert["valide_n1"] = $valide_n1;
                    $insert["validation_n1"] = $validation_n1;
                } else {
                    $insert["valide"]=$valide_n2;
                    $insert["validation"]=$validation_n2;
                }

                if ($this->id) {
                    $insert["id_origin"] = $this->id;
                }

                $db = new db();
                $db->CSRFToken = $this->CSRFToken;
                $db->insert("absences", $insert);
            }

            // Recherche du responsables pour l'envoi de notifications
            $a = new absences();
            $a->getResponsables($debutSQL, $finSQL, $agent->id());
            $responsables = $a->responsables;

            // Informations sur l'agent
            $nom = $agent->nom();
            $prenom = $agent->prenom();

            // Choix des destinataires des notifications selon la configuration
            if ($GLOBALS['config']['Absences-notifications-agent-par-agent']) {
                $a=new absences();
                $a->getRecipients2(null, $agent->id(), $notifications, 500, $debutSQL, $finSQL);
                $destinataires = $a->recipients;
            } else {
                $a = new absences();
                $a->getRecipients($notifications, $responsables, $agent);
                $destinataires = $a->recipients;
            }

            // Récupération de l'ID de l'absence enregistrée pour la création du lien dans le mail
            $info = array(array("name"=>"MAX(id)", "as"=>"id"));
            $where = array("debut"=>$debut_sql, "fin"=>$fin_sql, "perso_id"=>$agent->id());
            $db = new db();
            $db->select2("absences", $info, $where);
            if ($db->result) {
                $id = $db->result[0]['id'];
            }

            // Recherche des plages de SP concernées pour ajouter cette information dans le mail.
            $a = new absences();
            $a->debut = $debut_sql;
            $a->fin = $fin_sql;
            $a->perso_ids = array($agent->id());
            $a->infoPlannings();
            $infosPlanning = $a->message;

            // N'envoie la notification que s'il s'agit d'un ajout simple, et non s'il s'agit d'un ajout qui suit la modification d'une récurrrence (exception ou modification des événements suivants sans modifier les précédents)
            // Si $this->uid : Ajout simple. Si !$this->uid : Modification, donc pas d'envoi de notification à ce niveau (envoyée via modif2.php)
            if (!$this->uid) {
                // Titre différent si titre personnalisé (config) ou si validation ou non des absences (config)
                if ($GLOBALS['config']['Absences-notifications-titre']) {
                    $titre = $GLOBALS['config']['Absences-notifications-titre'];
                } else {
                    $titre = $GLOBALS['config']['Absences-validation'] ? "Nouvelle demande d absence" : "Nouvelle absence";
                }

                // Si message personnalisé (config), celui-ci est inséré
                if ($GLOBALS['config']['Absences-notifications-message']) {
                    $message = "<b><u>{$GLOBALS['config']['Absences-notifications-message']}</u></b><br/>";
                } else {
                    $message = "<b><u>$titre</u></b> : ";
                }

                // On complète le message avec les informations de l'absence
                $message .= "<ul><li>Agent : <strong>$prenom $nom</strong></li>";
                $message .= "<li>Début : <strong>$debut";
                if ($hre_debut != "00:00:00") {
                    $message .= " ".heure3($hre_debut);
                }
                $message .= "</strong></li><li>Fin : <strong>$fin";
                if ($hre_fin != "23:59:59") {
                    $message .= " ".heure3($hre_fin);
                }
                $message .= "</strong></li>";

                if ($this->rrule) {
                    $rrule = recurrenceRRuleText($this->rrule);
                    $message .= "<li>Récurrence : $rrule</li>";
                }

                $message .= "<li>Motif : $motif";
                if ($motif_autre) {
                    $message .= " / $motif_autre";
                }
                $message .= "</li>";

                if ($GLOBALS['config']['Absences-validation']) {
                    $message .= "<li>Validation : $validationText</li>\n";
                }

                if ($commentaires) {
                    $message .= "<li>Commentaire: <br/>$commentaires</li>";
                }

                $message .= "</ul>";

                // Ajout des informations sur les plannings
                $message .= $infosPlanning;

                // Ajout du lien permettant de rebondir sur l'absence
                $url = $GLOBALS['config']['URL'] . "/absence/$id";
                $message .= "<p>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a></p>";

                // Envoi du mail
                $m = new CJMail();
                $m->subject = $titre;
                $m->message = $message;
                $m->to = $destinataires;
                $m->send();

                // Si erreur d'envoi de mail
                if ($m->error) {
                    $msg2 .= "<li>".$m->error_CJInfo."</li>";
                    $msg2_type = "error";
                }
            }
        }
        $this->msg2 = $msg2;
        $this->msg2_type = $msg2_type;
        $this->id = $id;

    }

    /**
    * @function calculHeuresAbsences
    * @param date string, date de début au format YYYY-MM-DD
    * Calcule les heures d'absences des agents pour la semaine définie par $date ($date = une date de la semaine)
    * Utilisée par planning::menudivAfficheAgent pour ajuster le nombre d'heure de SP à effectuer en fonction des absences
    */
    public function calculHeuresAbsences($date)
    {
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
        $db->select2("heures_absences", "*", array("semaine"=>$j1));
        $heuresAbsencesUpdate=0;
        if ($db->result) {
            $heuresAbsencesUpdate=$db->result[0]["update_time"];
            $heures=json_decode(html_entity_decode($db->result[0]["heures"], ENT_QUOTES|ENT_IGNORE, "UTF-8"), true);
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
        if ($aUpdate>$heuresAbsencesUpdate or $pUpdate>$heuresAbsencesUpdate or $pHUpdate>$heuresAbsencesUpdate) {
            // Recherche de toutes les absences
            $absences=array();
            $a =new absences();
            $a->valide=true;
            $a->unique=true;
            $a->fetch(null, null, $j1, $j7, null);
            if ($a->elements and !empty($a->elements)) {
                $absences=$a->elements;
            }
            // Recherche de tous les plannings de présence
            $edt=array();
            $ph=new planningHebdo();
            $ph->debut=$j1;
            $ph->fin=$j7;
            $ph->valide=true;
            $ph->fetch();
            if ($ph->elements and !empty($ph->elements)) {
                $edt=$ph->elements;
            }

            // Recherche des agents pour appliquer le pourcentage sur les heures d'absences en fonction du taux de SP
            $p=new personnel();
            $p->fetch();
            $agents=$p->elements;
      
            // Calcul des heures d'absences
            $heures=array();
            if (!empty($absences)) {
                // Pour chaque absence
                foreach ($absences as $key => $value) {
                    $perso_id=$value['perso_id'];
                    $h1=array_key_exists($perso_id, $heures)?$heures[$perso_id]:0;
      
                    // Si $h1 n'est pas un nombre ("N/A"), une erreur de calcul a été enregistrée. Donc on ne continue pas le calcul.
                    // $heures[$perso_id] restera "N/A"
                    if (!is_numeric($h1)) {
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
                    if (is_numeric($h)) {
                        $h=$h+$h1;
                    } else {
                        $h="N/A";
                    }

                    $heures[$perso_id]=$h;

                    // On applique le pourcentage
                    if (strpos($agents[$perso_id]["heures_hebdo"], "%")) {
                        $pourcent=(float) str_replace("%", null, $agents[$perso_id]["heures_hebdo"]);
                        $heures[$perso_id]=$heures[$perso_id]*$pourcent/100;
                    }
                }
            }

            // Enregistrement des heures dans la base de données
            $db=new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->delete("heures_absences", array("semaine"=>$j1));
            $db=new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->insert("heures_absences", array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heures)));
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
    public function calculTemps($debut, $fin, $perso_id)
    {
        $version=$GLOBALS['config']['Version'];

        $hre_debut=substr($debut, -8);
        $hre_fin=substr($fin, -8);
        $hre_fin=$hre_fin=="00:00:00"?"23:59:59":$hre_fin;
        $debut=substr($debut, 0, 10);
        $fin=substr($fin, 0, 10);

        // Calcul du nombre d'heures correspondant à une absence
        $current=$debut;
        $difference=0;

        // Pour chaque date
        while ($current<=$fin) {

      // On ignore les jours de fermeture
            $j = new ClosingDay();
            $j->fetchByDate($current);
            if (!empty($j->elements)) {
                foreach ($j->elements as $elem) {
                    if ($elem['fermeture']) {
                        $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
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
            if (!$GLOBALS['config']['PlanningHebdo']) {
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
            if (empty($p->elements)) {
                $this->error=true;
                $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
                return false;
            }

            // Sinon, on calcule les heures d'absence
            $d=new datePl($current);
            $semaine=$d->semaine3;
            $jour=$d->position?$d->position:7;
            $jour=$jour+(($semaine-1)*7)-1;

            $wh = new WorkingHours($p->elements[0]['temps']);
            $temps = $wh->hoursOf($jour);

            foreach ($temps as $t) {
                $t0 = strtotime($t[0]);
                $t1 = strtotime($t[1]);
        
                $debutAbsence1 = $debutAbsence > $t0 ? $debutAbsence : $t0;
                $finAbsence1 = $finAbsence < $t1 ? $finAbsence : $t1;
                if ($finAbsence1 > $debutAbsence1) {
                    $difference += $finAbsence1 - $debutAbsence1;
                }
            }

            $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
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
    public function calculTemps2()
    {
        $version=$GLOBALS['config']['Version'];

        $debut=$this->debut;
        $edt=$this->edt;
        $fin=$this->fin;
        $perso_id=$this->perso_id;

        $hre_debut=substr($debut, -8);
        $hre_fin=substr($fin, -8);
        $hre_fin=$hre_fin=="00:00:00"?"23:59:59":$hre_fin;
        $debut=substr($debut, 0, 10);
        $fin=substr($fin, 0, 10);

        // Calcul du nombre d'heures correspondant à une absence
        $current=$debut;
        $difference=0;

        // Pour chaque date
        while ($current<=$fin) {
            // On ignore les jours de fermeture
            if (!$this->ignoreFermeture) {
                $j = new ClosingDay();
                $j->fetchByDate($current);
                if (!empty($j->elements)) {
                    foreach ($j->elements as $elem) {
                        if ($elem['fermeture']) {
                            $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
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
            if (!$GLOBALS['config']['PlanningHebdo']) {
                $this->error=true;
                $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
                $this->minutes="N/A";
                $this->heures="N/A";
                $this->heures2="N/A";
                return false;
            }

            // On consulte le planning de présence de l'agent
            if ($GLOBALS['config']['PlanningHebdo']) {
                $version = $GLOBALS['version'];
                require_once __DIR__."/../planningHebdo/class.planningHebdo.php";

                $edt=array();
                if ($this->edt and !empty($this->edt)) {
                    foreach ($this->edt as $elem) {
                        if ($elem['perso_id'] == $perso_id) {
                            $edt=$elem;
                            break;
                        }
                    }
                }

                // Si le planning n'est pas validé pour l'une des dates, on retourne un message d'erreur et on arrête le calcul
                if (empty($edt)) {
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
      

            $wh = new WorkingHours($edt['temps']);
            $temps = $wh->hoursOf($jour);

            foreach ($temps as $t) {
                $t0 = strtotime($t[0]);
                $t1 = strtotime($t[1]);
        
                $debutAbsence1 = $debutAbsence > $t0 ? $debutAbsence : $t0;
                $finAbsence1 = $finAbsence < $t1 ? $finAbsence : $t1;
                if ($finAbsence1 > $debutAbsence1) {
                    $difference += $finAbsence1 - $debutAbsence1;
                }
            }

            $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
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
    public function check($perso_id, $debut, $fin, $valide=true)
    {
        if (strlen($debut)==10) {
            $debut.=" 00:00:00";
        }

        if (strlen($fin)==10) {
            $fin.=" 23:59:59";
        }

        $filter=array("perso_id"=>$perso_id, "debut"=>"<$fin", "fin"=>">$debut");
    
        if ($valide==true or $GLOBALS['config']['Absences-validation']==0) {
            $filter["valide"]=">0";
        }
    
        $db=new db();
        $db->select2("absences", null, $filter);
        if ($db->result) {
            return true;
        }
        return false;
    }

    public function deleteAllDocuments() {
        if (!$this->id) return;
        $entityManager = $GLOBALS['entityManager'];
        $absdocs = $entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $this->id]);
        foreach ($absdocs as $absdoc) {
            $absdoc->deleteFile();
            $entityManager->remove($absdoc);
        }
        $entityManager->flush();

        $absenceDocument = new AbsenceDocument();
        if (is_dir($absenceDocument->upload_dir() . $this->id)) {
            rmdir($absenceDocument->upload_dir() . $this->id);
        }
    }

    public function fetch($sort="`debut`,`fin`,`nom`,`prenom`", $agent=null, $debut=null, $fin=null, $sites=null)
    {
        $entityManager = $GLOBALS['entityManager'];

        $filter="";
        //	DB prefix
        $dbprefix=$GLOBALS['config']['dbprefix'];
        // Date, debut, fin
        $date=date("Y-m-d");
        if ($debut) {
            $fin=$fin?$fin:$date;
            if (strlen($fin)==10) {
                $fin=$fin." 23:59:59";
            }
            $dates="`debut`<='$fin' AND `fin`>='$debut'";
        } else {
            $dates="`fin`>='$date'";
        }

        if ($this->valide and $GLOBALS['config']['Absences-validation']) {
            $filter.=" AND `{$dbprefix}absences`.`valide`>0 ";
        }

        if (!$this->rejected) {
            $filter.=" AND `{$dbprefix}absences`.`valide` != -1 ";
        }

        // Don't look for teleworking absences if the position is compatible with
        if ($this->teleworking == false ) {
            $absence_reasons = $entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
            $teleworking_reasons = array();
            foreach ($absence_reasons as $reason) {
                $teleworking_reasons[] = $reason->valeur();
            }

            $filter .= " AND `motif` NOT IN ('" . implode("','", $teleworking_reasons) . "') ";
        }

        // N'affiche que les absences des agents non supprimés par défaut : $this->agents_supprimes=array(0);
        // Affiche les absences des agents supprimés si précisé : $this->agents_supprimes=array(0,1) ou array(0,1,2)
        $deletedAgents=implode("','", $this->agents_supprimes);
        $filter.=" AND `{$dbprefix}personnel`.`supprime` IN ('$deletedAgents') ";

        // Sort
        $sort=$sort?$sort:"`debut`,`fin`,`nom`,`prenom`";

	if (is_numeric($agent) and $agent !=0) {
            $filter.=" AND `{$dbprefix}personnel`.`id` = '$agent' ";
	}

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
        if ($db->result) {
            foreach ($db->result as $elem) {
      
        // Multisites, n'affiche que les agents des sites choisis
                if (!empty($sites)) {
                    if ($GLOBALS['config']['Multisites-nombre'] > 1) {
                        $sitesAgent = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    } else {
                        $sitesAgent = array(1);
                    }
          
                    $keep = false;

                    if (is_array($sitesAgent)) {
                        foreach ($sites as $site) {
                            if (in_array($site, $sitesAgent)) {
                                $keep = true;
                                break;
                            }
                        }
                    }

                    if ($keep === false) {
                        continue;
                    }
                }

                // Gestion des groupes : ajout des infos sur les autres agents et affichage d'une seule ligne si $this->groupe=true
                $groupe = null;
                if (!empty($elem['groupe'])) {
                    // Le groupe est complété de la date et heure de début et de fin pour qu'il soit unique pour chaque occurence (si récurrence)
                    $groupe = $elem['groupe'].$elem['debut'].$elem['fin'];
                }
        
                // N'ajoute qu'une ligne pour les membres d'un groupe si $this->true
                if ($this->groupe and $groupe and in_array($groupe, $groupes)) {
                    continue;
                }

                // Ajoute des infos sur les autres agents
                if ($groupe) {
                    // Pour ne plus afficher les membres du groupe par la suite
                    $groupes[]=$groupe;
      
                    // Ajoute les ID des autres agents appartenant à ce groupe
                    $perso_ids=array();
                    $agents=array();
                    foreach ($db->result as $elem2) {
                        $groupe2 = $elem2['groupe'].$elem2['debut'].$elem2['fin'];
                        if ($groupe2 == $groupe) {
                            $perso_ids[]=$elem2['perso_id'];
                            $agents[]=$elem2['nom']." ".$elem2['prenom'];
                        }
                    }
                    $elem['perso_ids']=$perso_ids;
                    sort($agents);
                    $elem['agents']=$agents;
                } else {
                    $elem['perso_ids'][]=$elem['perso_id'];
                    $elem['agents'][]=$elem['nom']." ".$elem['prenom'];
                }

                // Le champ commentaires peut comporter des <br/> ou équivalents HTML lorsqu'il est importé depuis un fichier ICS. On les remplace par \n
                $elem['commentaires'] = str_replace(array('<br/>','&lt;br/&gt;'), "\n", $elem['commentaires']);

                $tmp=$elem;
                $debut=dateFr(substr($elem['debut'], 0, 10));
                $fin=dateFr(substr($elem['fin'], 0, 10));
                $debutHeure=substr($elem['debut'], -8);
                $finHeure=substr($elem['fin'], -8);
                if ($debutHeure=="00:00:00" and $finHeure=="23:59:59") {
                    $debutHeure=null;
                    $finHeure=null;
                } else {
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
        if (is_array($all) and $agent) {
            $result=array();

            foreach ($all as $elem) {
                if (is_numeric($agent)) {
                    if (in_array($agent, $elem['perso_ids'])) {
                        $result[]=$elem;
                    }
                } else {
                    foreach ($elem['agents'] as $a) {
                        if (pl_stristr($a, $agent)) {
                            $result[]=$elem;
                        }
                    }
                }
            }
        }
    
        // Filtre Unique : supprime les absences qui se chevauchent pour ne pas les compter plusieurs fois dans les calculs.
        // Ce filtre ne doit être utilisé que pour le calcul des heures et avec le filtre valide=true

        if ($this->unique) {
            usort($result, 'cmp_perso_debut_fin');
            $cles_a_supprimer = array();
      
            $last = 0;
            for ($i=1; $i<count($result); $i++) {
      
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
        
                if ($result[$i]['perso_id'] == $result[$last]['perso_id'] and $result[$i]['debut'] < $result[$last]['fin']) {
                    if ($result[$i]['debut'] >= $result[$last]['debut'] and $result[$i]['fin'] <= $result[$last]['fin']) {
                        $cles_a_supprimer[] = $i;
                    } elseif ($result[$i]['debut'] == $result[$last]['debut'] and $result[$i]['fin'] > $result[$last]['fin']) {
                        $cles_a_supprimer[] = $last;
                        $last = $i;
                    } elseif ($result[$i]['debut'] > $result[$last]['debut'] and $result[$i]['fin'] > $result[$last]['fin']) {
                        $result[$last]['fin']=$result[$i]['debut'];
                        $last = $i;
                    } else {
                        $last = $i;
                    }
                } else {
                    $last = $i;
                }
            }
            foreach ($cles_a_supprimer as $elem) {
                unset($result[$elem]);
            }
        }
    
        if ($result) {
            $this->elements=$result;
        }
    }

    public function fetchForStatistics($debut=null, $fin=null)
    {
        $filter = "";

        //	DB prefix
        $dbprefix = $GLOBALS['config']['dbprefix'];

        // Date, debut, fin
        $date = date("Y-m-d");
        if ($debut) {
            $fin = $fin ? $fin : $date;
            $dates = "`debut`<='$fin' AND `fin`>='$debut'";
        } else {
            $dates = "`fin`>='$date'";
        }

        if ($this->valide and $GLOBALS['config']['Absences-validation']) {
            $filter .= " AND `{$dbprefix}absences`.`valide`>0 ";
        }

        //	Select All
        $req="SELECT `{$dbprefix}absences`.`perso_id` AS `perso_id`, "
        ."`{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}absences`.`debut` AS `debut`, "
        ."`{$dbprefix}absences`.`fin` AS `fin`, `{$dbprefix}absences`.`motif` AS `motif` "
        ."FROM `{$dbprefix}absences` "
        ."WHERE $dates $filter;";

        $db = new db();
        $db->query($req);

        $all = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $all[ $elem['perso_id'] ][] = $elem;
            }
        }

        if ($all) {
            $this->elements=$all;
        }
    }

    public function fetchById($id)
    {
        // Search absence by Id
        $db=new db();
        $db->selectInnerJoin(
            array("absences","perso_id"),
            array("personnel","id"),
            array("id","debut","fin","motif","motif_autre","commentaires","valide_n1","validation_n1","pj1","pj2","so","demande","groupe","ical_key","cal_name","rrule","uid",
            array("name"=>"valide","as"=>"valide_n2"),array("name"=>"validation","as"=>"validation_n2")),
            array("nom","prenom","sites",array("name"=>"id","as"=>"perso_id"),"mail","mails_responsables"),
            array("id"=>$id)
        );

        // If no result, search by id_origin (used when updating recurring absences)
        // Several records may have the same id_origin, therefore results are sort by begin/end to keep only the first one (only begin and end may change among these records)
        if (!$db->result) {
            $db=new db();
            $db->selectInnerJoin(
                array("absences","perso_id","id"),
                array("personnel","id"),
                array("id","debut","fin","motif","motif_autre","commentaires","valide_n1","validation_n1","pj1","pj2","so","demande","groupe","ical_key","cal_name","rrule","uid",
                array("name"=>"valide","as"=>"valide_n2"),array("name"=>"validation","as"=>"validation_n2")),
                array("nom","prenom","sites",array("name"=>"id","as"=>"perso_id"),"mail","mails_responsables"),
                array("id_origin"=>$id),
                array(),
                "order by debut, fin"
            );
        }

        if ($db->result) {
            $result=$db->result[0];
            $result['mails_responsables']=explode(";", html_entity_decode($result['mails_responsables'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));

            // Créé un tableau $agents qui sera placé dans $this->elements['agents']
            // Ce tableau contient un tableau par agent avec les informations le concernant (nom, prenom, mail, etc.)
            // En cas d'absence enregistrée pour plusieurs agents, il sera complété avec les informations des autres agents
            $sites = json_decode(html_entity_decode($result['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $agents=array(array("perso_id"=>$result['perso_id'], "nom"=>$result['nom'], "prenom"=>$result['prenom'], "sites"=>$sites, "mail"=>$result['mail'], "mails_responsables"=>$result['mails_responsables'], "absence_id"=>$id));
            $perso_ids=array($result['perso_id']);

            // Absence concernant plusieurs agents
            // Complète le tableau $agents
            if ($result['groupe']) {
                $groupe=$result['groupe'];
                $debut=$result['debut'];
                $fin=$result['fin'];
                $agents=array();

                // Recherche les absences enregistrées sous le même groupe et les infos des agents concernés
                $db=new db();
                $db->selectInnerJoin(
                    array("absences","perso_id"),
                    array("personnel","id"),
                    array("id"),
                    array("nom","prenom","sites",array("name"=>"id","as"=>"perso_id"),"mail","mails_responsables"),
                    array("groupe"=>$groupe, "debut"=>$debut, "fin"=>$fin),
                    array(),
                    "order by nom, prenom"
                );

                // Complète le tableau $agents
                if ($db->result) {
                    foreach ($db->result as $elem) {
                        $elem['mails_responsables']=explode(";", html_entity_decode($elem['mails_responsables'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                        $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                        $agent=array("perso_id"=>$elem['perso_id'], "nom"=>$elem['nom'], "prenom"=>$elem['prenom'], "sites"=>$sites, "mail"=>$elem['mail'], "mails_responsables"=>$elem['mails_responsables'], "absence_id"=>$elem['id']);
                        if (!in_array($agent, $agents)) {
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
            $this->id = $id;
        }
    }


    public function getResponsables($debut=null, $fin=null, $perso_id, $droit = 200)
    {
        $responsables=array();
        $droitsAbsences=array();
        //	Si plusieurs sites et agents autorisés à travailler sur plusieurs sites, vérifions dans l'emploi du temps quels sont les sites concernés par l'absence
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $db=new db();
            $db->select("personnel", "temps", "id='$perso_id'");
            $temps=json_decode(html_entity_decode($db->result[0]['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $date=$debut;
            while ($date<=$fin) {
                // Emploi du temps si module planningHebdo activé
                if ($GLOBALS['config']['PlanningHebdo']) {
                    $version = $GLOBALS['version'];
                    include_once __DIR__."/../planningHebdo/class.planningHebdo.php";
                    $p=new planningHebdo();
                    $p->perso_id=$perso_id;
                    $p->debut=$date;
                    $p->fin=$date;
                    $p->valide=true;
                    $p->fetch();

                    if (empty($p->elements)) {
                        $temps=array();
                    } else {
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
                if (is_array($temps)) {
                    if (array_key_exists($j, $temps) and array_key_exists(4, $temps[$j])) {
                        $site = intval($temps[$j][4]);
                    }
                }
                // Ajout du numéro du droit correspondant à la gestion des absences de ce site
                if ($site and !in_array(($droit + $site), $droitsAbsences)) {
                    $droitsAbsences[] = $droit + $site;
                }
                $date=date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }

            // Si les jours d'absences ne concernent aucun site, on ajoute les responsables de tous les sites par sécurité
            if (empty($droitsAbsences)) {
                for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
                    $droitsAbsences[] = $droit + $i;
                }
            }
        }
        // Si un seul site, le droit de gestion des absences est 201
        else {
            $droitsAbsences[] = $droit + 1;
        }

        $db=new db();
        $db->select("personnel", null, "supprime='0'");
        foreach ($db->result as $elem) {
            $d=json_decode(html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            foreach ($droitsAbsences as $elem2) {
                if (is_array($d) and in_array($elem2, $d) and !in_array($elem, $responsables)) {
                    $responsables[]=$elem;
                }
            }
        }
        $this->responsables=$responsables;
    }

    public function getRecipients($validation, $responsables, App\Model\Agent $agent, $type = 'Absences')
    {
        /*
        Retourne la liste des destinataires des notifications en fonction du niveau de validation.
        $validation = niveau de validation (int) :
          1 : enregistrement d'une nouvelle absences
          2 : modification d'une absence sans validation ou suppression
          3 : validation N1
          4 : validation N2
        $responsables : listes des agents (array) ayant le droit de gérer les absences
        $agent : Model\Agent object
        */

        $categories=$GLOBALS['config']["{$type}-notifications{$validation}"];
        $categories=json_decode(html_entity_decode(stripslashes($categories), ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
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
        $mail = $agent->mail();
        $mails_responsables = $agent->get_manager_emails();

        // Agents ayant le droits de gérer les absences
        if (in_array(0, $categories)) {
            foreach ($responsables as $elem) {
                if (!in_array(trim(html_entity_decode($elem['mail'], ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                    $recipients[]=trim(html_entity_decode($elem['mail'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                }
            }
        }

        // Responsables directs
        if (in_array(1, $categories)) {
            if (is_array($mails_responsables)) {
                foreach ($mails_responsables as $elem) {
                    if (!in_array(trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                        $recipients[]=trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                    }
                }
            }
        }

        // Cellule planning
        if (in_array(2, $categories)) {
            $mailsCellule = $agent->get_planning_unit_mails();

            if (is_array($mailsCellule)) {
                foreach ($mailsCellule as $elem) {
                    if (!in_array(trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                        $recipients[]=trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                    }
                }
            }
        }

        // L'agent
        if (in_array(3, $categories)) {
            if (!in_array(trim(html_entity_decode($mail, ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                $recipients[]=trim(html_entity_decode($mail, ENT_QUOTES|ENT_IGNORE, "UTF-8"));
            }
        }

        $this->recipients=$recipients;
    }


  
    /** @function getRecipients2
     * Si le paramètre "Absences-notifications-agent-par-agent" est coché,
     * les notifications de modification d'absence sans validation sont envoyés aux responsables enregistrés dans dans la page Validations / Notifications
     * Les absences validées au niveau 1 sont envoyés aux agents ayant le droit de validation niveau 2
     * Les absences validées au niveau 2 sont envoyés aux agents concernés par l'absence
     * @param array agents_tous, tableau indéxé contenant le mail des agents
     * @param array $agents, tableau contenant les infos sur les agents concernés par l'absence
     * @param int $notifications, niveau de notification (1,2,3,4)
     * @param int $droit, droit à contrôler pour l'envoi de notification numéro 3 (exemple : 500 pour notifier les agents ayant le droit de validation d'absence niveau 2)
     * @param string debut, date de début d'absence au format YYYY-MM-DD HH:ii:ss
     * @param string fin, date de fin d'absence au format YYYY-MM-DD HH:ii:ss
     * @return array $recipients, tableau contenant les mails des agents à notifier
     */
    public function getRecipients2($agents_tous, $agents, $notifications, $droit = 500, $debut = null, $fin = null)
    {

    // Si le tableau contenant les informations sur les agents n'est pas fourni, on le créé
        if (! is_array($agents_tous)) {
            $p=new personnel();
            $p->supprime = array(0,1,2);
            $p->responsablesParAgent = true;
            $p->fetch();
            $agents_tous = $p->elements;
        }

        // Adaptation du tableau $agents s'il n'est pas conforme aux attentes
        if (is_array($agents)) {
            $keys = array_keys($agents);

            // Si le tableau fourni pour les agents ne contient que les IDs, on le complète
            if (! array_key_exists('mail', $agents[$keys[0]])) {
                $tmp = array();
                foreach ($agents as $elem) {
                    $tmp[$elem] = $agents_tous[$elem];
                }
                $agents = $tmp;
            }

            // Si le tableau des agents n'est pas indexé
            if (isset($agents[$keys[0]]['perso_id']) and $keys[0] != $agents[$keys[0]]['perso_id']) {
                $tmp = array();
                foreach ($agents as $elem) {
                    $tmp[$elem['perso_id']] = $agents_tous[$elem['perso_id']];
                }
                $agents = $tmp;
            }
        }

        // Si agents n'est pas un tableau, mais un seul ID
        if (! is_array($agents)) {
            $agents = array($agents => $agents_tous[$agents]);
        }

        $destinataires = array();

        switch ($notifications) {

      // Si l'absence est ajoutée ou modifiée sans validation, envoi de la notification aux responsables enregistrés dans la page Validations / Notifications
      case 1:
      case 2:

        foreach ($agents as $agent) {
            foreach ($agent['responsables'] as $elem) {
                if ($elem['notification'] and !in_array($agents_tous[$elem['responsable']]['mail'], $destinataires)) {
                    $destinataires[] = $agents_tous[$elem['responsable']]['mail'];
                }
            }
        }
        break;

      // Si l'absence est validée au niveau 1, envoi de la notification aux agents ayant le droit de validation niveau 2
      // Droits de gestion des absences niveau 2 : 50x
      case 3:

        // Si $droit == 1200, on recherche les responsables niveau 2 des planning de présence
        if ($droit == 1200) {
            $db = new db();
            $db->select2('personnel', 'mail', array('droits' => 'LIKE%1201%'));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $destinataires[] = $elem['mail'];
                }
            }
        } else {
            // Si $droit != 1200, on recherche les responsables des absences (niveau 2)
            foreach ($agents as $agent) {
                $a = new absences();
                $a->getResponsables($debut, $fin, $agent['id'], $droit);

                foreach ($a->responsables as $elem) {
                    if (!in_array($elem['mail'], $destinataires)) {
                        $destinataires[] = $elem['mail'];
                    }
                }
            }
        }


        break;

      // Si l'absence est validée au niveau 2, envoi de la notification aux agents concernés par l'absence
      case 4:

        foreach ($agents as $agent) {
            if (!in_array($agent['mail'], $destinataires)) {
                $destinataires[] = $agent['mail'];
            }
        }
        break;
    }

        $this->recipients = $destinataires;
    }


    /**
     * @function ics_add_event
     * Enregistre un événement dans le fichier ICS "Planning Biblio" de l'agent sélectionné
     * @params : tous les éléments d'une absence : date et heure de début et de fin, motif, commentaires, validation, ID de l'agent, règle de récurrence (rrule)
     * @param string $this->exdate : doit être la ligne complète commençant par EXDATE et finissant par \n
     */
    public function ics_add_event()
    {

    // Initilisation des variables, adaptation des valeurs
        $perso_id = $this->perso_id;
        $folder = sys_get_temp_dir();
        $file = "$folder/PBCalendar-$perso_id.ics";
        $tzid = date_default_timezone_get();
        $dtstart = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3$2$1', $this->debut).'T';
        $dtstart .= preg_replace('/(\d+):(\d+):(\d+)/', '$1$2$3', $this->hre_debut);
        $dtend = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3$2$1', $this->fin).'T';
        $dtend .= preg_replace('/(\d+):(\d+):(\d+)/', '$1$2$3', $this->hre_fin);
        $dtstamp = !empty($this->dtstamp) ? $this->dtstamp : gmdate('Ymd\THis\Z');
        $summary = $this->motif_autre ? html_entity_decode($this->motif_autre, ENT_QUOTES|ENT_IGNORE, 'UTF-8') : html_entity_decode($this->motif, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $cal_name = !empty($this->cal_name) ? $this->cal_name : "PlanningBiblio-Absences-$perso_id-$dtstamp";
        $uid = !empty($this->uid) ? $this->uid : $dtstart."_".$dtstamp;
        $status = $this->valide_n2 > 0 ? 'CONFIRMED' : 'TENTATIVE';

        // Description : en supprime les entités HTML et remplace les saut de lignes par des <br/> pour facilité le traitement des saut de lignes à l'affichage et lors des remplacements
        $description = html_entity_decode($this->commentaires, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $description = str_replace("\n", "<br/>", $description);

        // Gestion des groupes et des validations, utilisation du champ CATEGORIES
        $categories = array();
        if ($this->groupe) {
            $categories[] = "PBGroup=".$this->groupe;
        }
        if ($this->valide_n1) {
            $categories[] = "PBValideN1=".$this->valide_n1;
        }
        if ($this->validation_n1) {
            $categories[] = "PBValidationN1=".$this->validation_n1;
        }
        if ($this->valide_n2) {
            $categories[] = "PBValideN2=".$this->valide_n2;
        }
        if ($this->validation_n2) {
            $categories[] = "PBValidationN2=".$this->validation_n2;
        }

        if ($this->id) {
            $categories[] = "PBIDOrigin={$this->id}";
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
        $ics_content .= "X-LAST-MODIFIED-STRING:{$this->last_modified}\n";
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
        if ($this->exdate) {
            $ics_content .= $this->exdate;
        }
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
    public function ics_add_exdate($date)
    {
        $this->ics_get_event();
        $ics_event = $this->elements;
        $perso_id = $this->perso_id;
    
        if ($ics_event) {
            // On modifie la date LAST-MODIFIED
            $ics_event = preg_replace("/LAST-MODIFIED:.[^\n]*\n/", "LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n", $ics_event);

            // On modifie l'événement en ajoutant une exception
            if (strpos($ics_event, 'EXDATE')) {
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
    public function ics_delete_event()
    {
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
    public function ics_get_event()
    {
  
    // Récupère l'événement depuis la base de données
        $where = array('uid' => $this->uid);
        if (!empty($this->perso_id)) {
            $where['perso_id'] = $this->perso_id;
        }

        $db = new db();
        $db->sanitize_string = false;
        $db->select2('absences_recurrentes', 'event', $where);
    
        if ($db->result) {
            $this->elements = $db->result[0]['event'];
        }
    }


    /** @function ics_update_event();
     * @param string $this->uid : UID d'un événement ICS "Planning Biblio" (ex: 20171110120000_20171110115523Z)
     * @param int $this->perso_ids : IDs des agents
     * @params : tous les éléments d'une absence : date et heure de début et de fin (format FR JJ/MM/YYYY et hh:mm:ss), motif, commentaires, validation, ID de l'agent, règle de récurrence (rrule)
     * @desc : modifie un événement ICS "Planning Biblio"
     */
    public function ics_update_event()
    {
    // Recherche de l'événement pour récupèrer la date de départ pour la création des événements des agents ajoutés
        // le tableau $event servira aussi à la suppression des agents retirés de l'événement
        $db = new db();
        $db->select2('absences_recurrentes', 'event,perso_id', array('uid' => $this->uid));
        $event = $db->result;

        if (empty($event)) {
            return false;
        }
        // Récupération de la date de début de la série
        preg_match('/DTSTART.*:(\d*)T\d*\n/', $event[0]['event'], $matches);
        $debut = date('d/m/Y', strtotime($matches[1]));
        preg_match('/DTEND.*:(\d*)T\d*\n/', $event[0]['event'], $matches);
        $fin = date('d/m/Y', strtotime($matches[1]));
        preg_match('/CREATED.*:(\d*T\d*Z)\n/', $event[0]['event'], $matches);
        $dtstamp = $matches[1];

        // Suppression des agents retirés de l'événement
        $to_delete = array();
        foreach ($event as $e) {
            if (!in_array($e['perso_id'], $this->perso_ids)) {
                $to_delete[]=$e['perso_id'];
            }
        }

        if (!empty($to_delete)) {
            $to_delete = implode(',', $to_delete);

            // Suppression des événements dans la table absences_recurrentes
            $db = new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->delete('absences_recurrentes', array('uid' => $this->uid, 'perso_id' => "IN$to_delete"));

            // Suppression des événements dans la table absences
            $db = new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->delete('absences', array('perso_id'=> "IN$to_delete", 'uid'=> $this->uid, 'cal_name' => "LIKEPlanningBiblio-Absences-%"));
        }


        // Pour chaque agent, mise à jour ou ajout de l'événement
        foreach ($this->perso_ids as $perso_id) {
            $a = new absences();
            $a->perso_id = $perso_id;
            $a->uid = $this->uid;
            $a->ics_get_event();
            $ics_event = $a->elements;

            // Pour chaque agent, si l'agent faisait déjà partie de l'événement, on modifie les infos
            if ($ics_event) {
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
                if ($this->groupe) {
                    $tmp[] = "PBGroup={$this->groupe}";
                }
                if ($this->valide_n1) {
                    $tmp[] = "PBValideN1={$this->valide_n1}";
                }
                if ($this->validation_n1) {
                    $tmp[] = "PBValidationN1={$this->validation_n1}";
                }
                if ($this->valide_n2) {
                    $tmp[] = "PBValideN2={$this->valide_n2}";
                }
                if ($this->validation_n2) {
                    $tmp[] = "PBValidationN2={$this->validation_n2}";
                }

                if ($this->id) {
                    $tmp[] = "PBIDOrigin={$this->id}";
                }

                if (!empty($tmp)) {
                    $categories = 'CATEGORIES:'.implode(';', $tmp);
                    $categories = str_replace("\n", null, $categories)."\n";
                }

                if (strpos($ics_event, 'CATEGORIES:')) {
                    $ics_event = preg_replace("/CATEGORIES:.*\n/", "CATEGORIES:$categories\n", $ics_event);
                } else {
                    $ics_event = str_replace('END:VEVENT', "CATEGORIES:$categories\nEND:VEVENT", $ics_event);
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

                $ics_event = preg_replace("/X-LAST-MODIFIED-STRING:.*\n/", "X-LAST-MODIFIED-STRING:{$this->last_modified}\n", $ics_event);

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
                $a->dtstamp = $dtstamp;
                $a->perso_id = $perso_id;
                $a->commentaires = $this->commentaires;
                $a->debut = $debut;
                $a->fin = $fin;
                $a->hre_debut = $this->hre_debut;
                $a->hre_fin = $this->hre_fin;
                $a->demande = $demande;
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
    public function ics_update_table()
    {
        $db = new db();
        $db->select2('absences_recurrentes', null, array('end' => '0' , 'last_check' => "< CURDATE"));
        if ($db->result) {
            foreach ($db->result as $elem) {
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
    public function ics_update_until($datetime)
    {
        $this->ics_get_event();
        $ics_event = $this->elements;
        $perso_id = $this->perso_id;

        if ($ics_event) {

      // Mise à jour de LAST-MODIFIED
            $ics_event = preg_replace("/LAST-MODIFIED:.*\n/", "LAST-MODIFIED:".gmdate('Ymd\THis\Z')."\n", $ics_event);

            // On modifie ou ajoute une date de fin à RRULE
            preg_match("/\nRRULE:(.*)\n/", $ics_event, $matches);
            $rrule = substr($matches[0], 7);
            $rrule = str_replace("\n", null, $rrule);

            if (strpos($rrule, 'UNTIL')) {
                $rrule = preg_replace("/UNTIL=\d+T\d+Z/", "UNTIL=$datetime", $rrule);
            } elseif (strpos($rrule, 'COUNT')) {
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
    public function infoPlannings()
    {
        $version="absences";
        require_once "postes/class.postes.php";
  
        $debut=dateSQL($this->debut);
        $fin=dateSQL($this->fin);
        $perso_ids=implode(",", $this->perso_ids);

        $dateDebut=substr($debut, 0, 10);
        $dateFin=substr($fin, 0, 10);
    
        $heureDebut=substr($debut, 11);
        $heureFin=substr($fin, 11);

        // Recherche des plages de SP concernées pour ajouter cette information dans le mail.
        // Recherche des plannings validés
        $plannings_valides=array();
        $db=new db();
        $db->select2("pl_poste_verrou", "date", array("date"=>"BETWEEN $dateDebut AND $dateFin","verrou2"=>"1"));
        if ($db->result) {
            foreach ($db->result as $elem) {
                $plannings_valides[]=$elem['date'];
            }
        }

        sort($plannings_valides);

        // nom des postes
        $p=new postes();
        $p->fetch();
        $postes=$p->elements;
    
        // Nom des sites
        $sites=array(1=>null);
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
                $sites[$i]=$GLOBALS['config']["Multisites-site$i"];
            }
        }

        // Recherche des plannings dans lequel apparaît l'agent
        $plannings=array();
        $db=new db();
        $db->select2("pl_poste", null, array("date"=>"BETWEEN $dateDebut AND $dateFin","perso_id"=>"IN $perso_ids"), "ORDER BY date,debut,fin");
        if ($db->result) {
            foreach ($db->result as $elem) {
                // On exclu les créneaux horaires qui sont en dehors de l'absences
                if ($elem['date']==$dateDebut and $elem['fin']<=$heureDebut) {
                    continue;
                }
                if ($elem['date']==$dateFin and $elem['debut']>=$heureFin) {
                    continue;
                }

                $elem['valide']=in_array($elem['date'], $plannings_valides)?" (Valid&eacute;)":null;
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
        if (!empty($plannings)) {
            // Fusionne les plages horaires si sur le même poste sur des plages successives
            $tmp=array();
            $j=0;
            for ($i=0; $i<count($plannings);$i++) {
                if ($i==0) {
                    $tmp[$j]=$plannings[$i];
                } elseif ($plannings[$i]['site']==$tmp[$j]['site'] and $plannings[$i]['poste']==$tmp[$j]['poste']
        and $plannings[$i]['debut']==$tmp[$j]['fin']) {
                    $tmp[$j]['fin']=$plannings[$i]['fin'];
                } else {
                    $j++;
                    $tmp[$j]=$plannings[$i];
                }
            }
            $plannings=$tmp;
      
            // Rédaction du message
            $message="<p><strong>Les plannings suivants sont affect&eacute;s par cette absence :</strong><ul>\n";
            $lastDate=null;
            foreach ($plannings as $elem) {
                if ($elem['date']!=$lastDate and $lastDate!=null) {
                    $message.="</ul></li>\n";
                }
                if ($elem['date']!=$lastDate) {
                    $message.="<li><strong>{$elem['date']}{$elem['valide']}</strong><ul>\n";
                }
                $message.="<li>{$elem['debut']}-{$elem['fin']} {$elem['site']} {$elem['poste']}</li>\n";
                $lastDate=$elem['date'];
            }
            $message.="</ul></li></ul></p>\n";
        }
    
        $this->message=$message;
    }

    public function piecesJustif($id, $pj, $checked)
    {
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("absences", array($pj => $checked), array("id"=>$id));
    }

    /** roles
     * @param int $perso_id : ID de l'agent concerné par l'absence
     * @param boolean $accessDenied default false : afficher "accès refusé" si la page demandée ne concerne pas l'agent logué et s'il n'est pas admin
     * @return Array($adminN1, $adminN2) : tableau ayant pour 1ère valeur true si l'agent logué est adminN1, false sinon, pour 2ème valeur true s'il est adminN2, false sinon
     * Affiche "accès refusé" si la page demandée ne concerne pas l'agent logué et s'il n'est pas admin
     */
    public function roles($perso_id, $accesDenied = false)
    {

        // Droits d'administration niveau 1 et niveau 2
        // Droits nécessaires en mono-site
        $droitsN1 = array(201);
        $droitsN2 = array(501);

        // Droits nécessaires en multisites avec vérification des sites attribués à l'agent concerné par l'absence
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $droitsN1 = array();
            $droitsN2 = array();

            $p=new personnel();
            $p->fetchById($perso_id);

            if (is_array($p->elements[0]['sites'])) {
                foreach ($p->elements[0]['sites'] as $site) {
                    $droitsN1[] = 200 + $site;
                    $droitsN2[] = 500 + $site;
                }
            }
        }

        // Ai-je le droit d'administration niveau 1 pour l'absence demandée
        $adminN1 = false;

        // Si le paramètre "Absences-notifications-agent-par-agent" est coché, vérification du droit N1 à partir de la table "responsables"
        if ($GLOBALS['config']['Absences-notifications-agent-par-agent']) {
            $db = new db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    if ($elem['perso_id'] == $perso_id) {
                        $adminN1 = true;
                        break;
                    }
                }
            }

        // Si le paramètre "Absences-notifications-agent-par-agent" n'est pascoché, vérification du droit N1 à partir des droits cochés dans la fiche de l'agent logué ($_SESSION['droits']
        } else {
            foreach ($droitsN1 as $elem) {
                if (in_array($elem, $_SESSION['droits'])) {
                    $adminN1 = true;
                    break;
                }
            }
        }

        // Ai-je le droit d'administration niveau 2 pour l'absence demandée
        $adminN2 = false;
        foreach ($droitsN2 as $elem) {
            if (in_array($elem, $_SESSION['droits'])) {
                $adminN2 = true;
                break;
            }
        }

        // Affiche accès refusé si l'absence ne concerne pas l'agent logué et qu'il n'est pas admin
        if ($accesDenied and !$adminN1 and !$adminN2 and $perso_id != $_SESSION['login_id']) {
            echo "<h3 style='text-align:center;'>Accès refusé</h3>\n";
            echo "<p style='text-align:center;' >\n";
            echo "<a href='javascript:history.back();'>Retour</a></p>\n";
            include(__DIR__.'/../include/footer.php');
        }

        return array($adminN1, $adminN2);
    }

    public function update_time()
    {
        $db=new db();
        $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['config']['dbprefix']}absences';");
        return $db->result[0]['Update_time'];
    }
}
