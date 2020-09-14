<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/function.php
Création : mai 2011
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié

Description :
Page contenant les fonctions PHP communes
Page appelée par les fichiers index.php, setup/index.php et planning/poste/menudiv.php
*/

use App\Model\Agent;
use App\PlanningBiblio\WorkingHours;

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "accessDenied.php";
    exit;
}

require_once(__DIR__."/../../vendor/phpmailer/phpmailer/src/Exception.php");
require_once(__DIR__."/../../vendor/phpmailer/phpmailer/src/PHPMailer.php");
require_once(__DIR__."/../../vendor/phpmailer/phpmailer/src/SMTP.php");
require_once(__DIR__."/../../vendor/phpmailer/phpmailer/src/OAuth.php");
require_once(__DIR__.'/../personnel/class.personnel.php');

use PHPMailer\PHPMailer\PHPMailer;

class datePl
{
    public $dates = null;
    public $jour = null;
    public $jour_complet = null;
    public $sam = null;
    public $sem = null;
    public $semaine = null;
    public $semaine3 = null;
    public $position = null;
  
    public function __construct($date)
    {
        $yyyy = (int) substr($date, 0, 4);
        $mm = (int) substr($date, 5, 2);
        $dd = (int) substr($date, 8, 2);
        $this->semaine=date("W", mktime(0, 0, 0, $mm, $dd, $yyyy));
        $this->sem=($this->semaine%2);
        $this->sam="semaine";
        $position=date("w", mktime(0, 0, 0, $mm, $dd, $yyyy));
        $this->position=$position;
        switch ($position) {
      case 1: $this->jour="lun";	$this->jour_complet="lundi";		break;
      case 2: $this->jour="mar";	$this->jour_complet="mardi";		break;
      case 3: $this->jour="mer";	$this->jour_complet="mercredi";		break;
      case 4: $this->jour="jeu";	$this->jour_complet="jeudi";		break;
      case 5: $this->jour="ven";	$this->jour_complet="vendredi";		break;
      case 6: $this->jour="sam";	$this->jour_complet="samedi";	$this->sam="samedi";			break;
      case 0: $this->jour="dim";	$this->jour_complet="dimanche";	$this->sam="dimanche";	$position=7;	break;
    }
    
        $j1=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+1-$position, $yyyy));
        $j2=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+2-$position, $yyyy));
        $j3=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+3-$position, $yyyy));
        $j4=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+4-$position, $yyyy));
        $j5=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+5-$position, $yyyy));
        $j6=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+6-$position, $yyyy));
        $j7=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+7-$position, $yyyy));
    
        $this->dates=array($j1,$j2,$j3,$j4,$j5,$j6,$j7);


        // Calcul du numéro de la semaine pour l'utilisation d'un seul planning hebdomadaire : toujours 1
        if ($GLOBALS['config']['nb_semaine']==1) {
            $this->semaine3=1;
        }
        // Calcul du numéro de la semaine pour l'utilisation de 2 plannings hebdomadaires
        if ($GLOBALS['config']['nb_semaine']==2) {
            $this->semaine3=$this->semaine%2?1:2;
        }
        // Calcul du numéro de la semaine pour l'utilisation de 3 plannings hebdomadaires
        if ($GLOBALS['config']['nb_semaine']==3) {
            $position=date("w", strtotime(dateSQL($GLOBALS['config']['dateDebutPlHebdo'])))-1;
            $position=$position==-1?6:$position;
            $dateFrom=new dateTime(dateSQL($GLOBALS['config']['dateDebutPlHebdo']));
            $dateFrom->sub(new DateInterval("P{$position}D"));

            $position=date("w", strtotime($date))-1;
            $position=$position==-1?6:$position;
            $dateNow=new dateTime($date);
            $dateNow->sub(new DateInterval("P{$position}D"));

            $interval=$dateNow->diff($dateFrom);
            $interval=$interval->format("%a");
            $interval=$interval/7;
            if (!($interval%3)) {
                $this->semaine3=1;
            }
            if (!(($interval+2)%3)) {
                $this->semaine3=2;
            }
            if (!(($interval+1)%3)) {
                $this->semaine3=3;
            }
        }
    }

    public function planning_day_index_for($agent_id)
    {
        $config = $GLOBALS['config'];
        $semaine = $this->semaine;
        $semaine3 = $this->semaine3;

        // Day of week. Mon = 0 ,Sun = 6
        $day = $this->position - 1;
        if ($day == -1) {
            $day = 6;
        }

        // Using 2 weekly schedule (even odd).
        // Even: position += 7, Mon A = 0, Mon B = 7, Sun B = 13
        if ($config['nb_semaine'] == '2' and !($semaine%2) and !$config['EDTSamedi']) {
            $day += 7;
        }
        // Using 3 weekly schedule.
        elseif ($config['nb_semaine'] == '3' and !$config['EDTSamedi']) {
            if ($semaine3 == 2) {
                $day += 7;
            } elseif ($semaine3 == 3) {
                $day += 14;
            }
        }

        // Using a schedule with Saturday and one without.
        if ($config['EDTSamedi']) {
            // Check if the current week has Saturday.
            $p=new personnel();
            $p->fetchEDTSamedi($agent_id, $this->dates[0], $this->dates[0]);
            $day += $p->offset;
        }

        return $day;
    }
}

class CJMail
{
    public $message=null;
    public $to=null;
    public $subject=null;
    public $error="";
    public $error_CJInfo=null;
    public $error_encoded=null;
    public $failedAddresses=array();
    public $successAddresses=array();

    public function __construct()
    {
    }


    private function prepare()
    {
  
    /* arrête la procédure d'envoi de mail si désactivé dans la config */
        if (!$GLOBALS['config']['Mail-IsEnabled']) {
            $this->error.="L'envoi des e-mails est désactivé dans la configuration\n";
            $this->successAddresses=array();
            $this->failedAddresses=$this->to;
            return false;
        }

        /* Met les destinataires dans un tableau s'ils sont dans une chaine de caractère séparée par des ; */
        if (!is_array($this->to)) {
            $this->to=explode(";", $this->to);
        }

        /* Vérifie que les e-mails sont valides */
        $to=array();
        $incorrect=array();
        foreach ($this->to as $elem) {
            if (verifmail(trim($elem))) {
                $to[]=trim($elem);
            } else {
                $incorrect[]=trim($elem);
                $this->failedAddresses[]=trim($elem);
            }
        }
        $this->to=$to;

        if (!empty($incorrect)) {
            $this->error.="Les adresses suivantes sont incorrectes : ".join(" ; ", $incorrect)."\n";
        }

        /* Arrête la procédure si aucun destinaire valide */
        if (empty($this->to)) {
            $this->error.="Aucun destinataire valide pour cet e-mail\n";
            return false;
        }

        /* Préparation du sujet */
        $this->subject = stripslashes($this->subject);
        $this->subject = "Planning : " . $this->subject;

        /* Préparation du message, html, doctype, signature */
        $message="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
        $message.="<html><head><title>Planning</title></head><body>";
        $message.= $this->message;
        $message.="<br/><br/>{$GLOBALS['config']['Mail-Signature']}<br/><br/>";
        $message.="</body></html>";
        $message = stripslashes($message);
        $message = str_replace(array("\n","\r\n\n","\r\n"), "<br/>", $message);

        $this->message = $message;
    }

  
    public function send()
    {
        if ($this->prepare()===false) {
            return false;
        }
    
        $mail = new PHPMailer();
        $mail->setLanguage('fr');
        if ($GLOBALS['config']['Mail-IsMail-IsSMTP']=="IsMail") {
            $mail->IsMail();
        } else {
            $mail->IsSMTP();
        }
        $mail->CharSet="utf-8";
        $mail->WordWrap =$GLOBALS['config']['Mail-WordWrap'];
        $mail->Hostname =$GLOBALS['config']['Mail-Hostname'];
        $mail->Host =$GLOBALS['config']['Mail-Host'];
        $mail->Port =$GLOBALS['config']['Mail-Port'];
        $mail->SMTPSecure = $GLOBALS['config']['Mail-SMTPSecure'];
        $mail->SMTPAuth =$GLOBALS['config']['Mail-SMTPAuth'];
        $mail->Username =$GLOBALS['config']['Mail-Username'];
        $mail->Password =decrypt($GLOBALS['config']['Mail-Password']);
        $mail->Sender =$GLOBALS['config']['Mail-From'];
        $mail->From =$GLOBALS['config']['Mail-From'];
        $mail->FromName =$GLOBALS['config']['Mail-FromName'];
        $mail->IsHTML();
    
        $mail->Body = $this->message;
    
        if (count($this->to)>1) {
            foreach ($this->to as $elem) {
                $mail->addBCC($elem);
            }
        } else {
            $mail->AddAddress($this->to[0]);
        }
    
        $mail->Subject = $this->subject;

        if (!$mail->Send()) {
            $this->error.=$mail->ErrorInfo ."\n";
      
            // error_CJInfo: pour affichage dans CJInfo (JS)
            $this->error_CJInfo=str_replace("\n", "#BR#", $this->error);
      
            // Liste des destinataires pour qui l'envoi a fonctionné (en cas de succès partiel)
            $this->successAddresses=$this->to;

            // Liste des destinataires pour qui l'envoi a échoué
            $pos=stripos($this->error, "SMTP Error: The following recipients failed: ");

            if ($pos!==false) {
                $failedAddr=substr($this->error, $pos+45);
                $end=strpos($failedAddr, "\n");
                $failedAddr=substr($failedAddr, 0, $end);
                $failedAddr=explode(", ", $failedAddr);

                $this->failedAddresses=array_merge($this->failedAddresses, $failedAddr);
    
                $this->successAddresses=array();
                foreach ($this->to as $elem) {
                    if (!in_array($elem, $failedAddr)) {
                        $this->successAddresses[]=$elem;
                    }
                }
            }
        } else {
            // Liste des destinataires pour qui l'envoi a fonctionné (en cas de succès total)
            $this->successAddresses=$this->to;
        }
    
        return true;
    }
}

function authSQL($login, $password)
{

    $auth = false;
    $em = $GLOBALS['entityManager'];

    $agent = $em->getRepository(Agent::class)->findBy(array('login' => $login, 'supprime' => 0));

    if (empty($agent) or empty($login)) {
        return false;
    }

    if (count($agent) <> 1) {
        return false;
    }

    $agent = $agent[0];

    // Old MD5 password
    if (strlen($agent->password()) == 32) {
        if (md5($password) == $agent->password()) {
            $auth=true;
            $_SESSION['oups']['Auth-Mode']="SQL";

            // Update password
            $bcrypt_password = password_hash($password,PASSWORD_BCRYPT);

            $agent->password($bcrypt_password);
            $em->persist($agent);
            $em->flush();
        }
    // New bcrypt password
    } else {
        if (password_verify($password, $agent->password())) {
            $auth=true;
            $_SESSION['oups']['Auth-Mode']="SQL";
        }
    }

    return $auth;
}

/**
* @function calculHeuresSP
* @param date string, date au format AAAA-MM-DD
* Calcul le nombre d'heures de SP que les agents doivent effectuer pour la semaine définie par $date
* Retourne le résultat sous forme d'un tableau array(perso_id1 => heures1, perso_id2 => heures2, ...)
* Stock le résultat (json_encode) dans la BDD table heures_sp
* Récupère et retourne le résultat à partir de la BDD si les tables personnel et planning_hebdo n'ont pas été modifiées
* pour gagner du temps lors des appels suivants.
* Fonction utilisée par planning::menudivAfficheAgents et dans le script statistiques/temps.php
*/
function calculHeuresSP($date, $CSRFToken)
{
    $config=$GLOBALS['config'];
    $version=$GLOBALS['version'];

    // Securité : Traitement pour une reponse Ajax
    if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $version='ajax';
    }
    require_once "horaires.php";
    require_once __DIR__."/../personnel/class.personnel.php";

    $d=new datePl($date);
    $dates=$d->dates;
    $semaine3=$d->semaine3;
    $j1=$dates[0];
    $j7=$dates[6];

    // Recherche des heures de SP des agents pour cette semaine
    // Recherche si les tableaux contenant les heures de SP existe
    $db=new db();
    $db->select2("heures_sp", "*", array("semaine"=>$j1));
    $heuresSPUpdate=0;
    if ($db->result) {
        $heuresSPUpdate=$db->result[0]["update_time"];
        $heuresSP=json_decode(html_entity_decode($db->result[0]["heures"], ENT_QUOTES|ENT_IGNORE, "utf-8"), true);
    }

    // Recherche des heures de SP avec le module planningHebdo
    if ($config['PlanningHebdo']) {
        require_once(__DIR__."/../planningHebdo/class.planningHebdo.php");

        // Vérifie si la table planning_hebdo a été mise à jour depuis le dernier calcul
        $p=new planningHebdo();
        $pHUpdate=strtotime($p->update_time());
    
        // Vérifie si la table personnel a été mise à jour depuis le dernier calcul
        $p=new personnel();
        $pUpdate=strtotime($p->update_time());

        // Si la table planning_hebdo a été modifiée depuis la création du tableaux des heures
        // Ou si les informations update_time n'existent pas ($phUpdate == null / $pUpdate == null)
        // Ou si le tableau des heures n'a pas été créé ($heuresSPUpdate=0), on le (re)fait.
        if ($pHUpdate>=$heuresSPUpdate or $pUpdate>=$heuresSPUpdate or $pHUpdate == null or $pUpdate == null) {
            $heuresSP=array();
    
            // Recherche de tous les agents pouvant faire du service public
            $p=new personnel();
            $p->supprime=array(0,1,2);
            $p->fetch("nom", "Actif");
      
            // Recherche de tous les plannings de présence
            $ph=new planningHebdo();
            $ph->debut=$j1;
            $ph->fin=$j7;
            $ph->valide=true;
            $ph->fetch();

            if (!empty($p->elements)) {
                // Pour chaque agents
                foreach ($p->elements as $key1 => $value1) {
                    $heuresSP[$key1]=$value1["heures_hebdo"];

                    if (strpos($value1["heures_hebdo"], "%")) {
                        $minutesHebdo=0;
                        if ($ph->elements and !empty($ph->elements)) {
                            // Calcul des heures depuis les plannings de présence
                            // Pour chaque jour de la semaine
                            foreach ($dates as $key2 => $jour) {
                                // On cherche le planning de présence valable pour chaque journée
                                foreach ($ph->elements as $edt) {
                                    if ($edt['perso_id']==$value1["id"]) {
                                        // Planning de présence trouvé
                                        if ($jour>=$edt['debut'] and $jour<=$edt['fin']) {
                                            // $pause = true si pause détectée le midi
                                            $pause=false;
                                            // Offset : pour semaines 1,2,3 ...
                                            $offset=($semaine3*7)-7;
                                            $key3=$key2+$offset;
                                            // Si heure de début et de fin de matiné
                                            if (array_key_exists($key3, $edt['temps']) and $edt['temps'][$key3][0] and $edt['temps'][$key3][1]) {
                                                $minutesHebdo+=diff_heures($edt['temps'][$key3][0], $edt['temps'][$key3][1], "minutes");
                                                $pause=true;
                                            }
                                            // Si heure de début et de fin d'après midi
                                            if (array_key_exists($key3, $edt['temps']) and $edt['temps'][$key3][2] and $edt['temps'][$key3][3]) {
                                                $minutesHebdo+=diff_heures($edt['temps'][$key3][2], $edt['temps'][$key3][3], "minutes");
                                                $pause=true;
                                            }
                                            // Si pas de pause le midi
                                            if (!$pause) {
                                                // Et heure de début et de fin de journée
                                                if (array_key_exists($key3, $edt['temps']) and $edt['temps'][$key3][0] and $edt['temps'][$key3][3]) {
                                                    $minutesHebdo+=diff_heures($edt['temps'][$key3][0], $edt['temps'][$key3][3], "minutes");
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $heuresRelles=$minutesHebdo/60;
                        // On applique le pourcentage
                        $pourcent=(float) str_replace("%", null, $value1["heures_hebdo"]);
                        $heuresRelles=$heuresRelles*$pourcent/100;
                        $heuresSP[$key1]=$heuresRelles;
                    }
                }
                // Utilisateur "Tout le monde"
                $heuresSP[2]=0;
            }
      
            // Enregistrement des horaires dans la base de données
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("heures_sp", array("semaine"=>$j1));
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("heures_sp", array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heuresSP)));
        }

        // Recherche des heures de SP sans le module planningHebdo
    } else {
        // Vérifie si la table personnel a été mise à jour depuis le dernier calcul
        $p=new personnel();
        $pUpdate=strtotime($p->update_time());

        // Si la table personnel a été modifiée depuis la Création du tableaux des heures
        // Ou si l'information update_time n'existe pas ($pUpdate == null)
        // Ou si le tableau des heures n'a pas été créé ($heuresSPUpdate=0), on le (re)fait.
        if ($pUpdate>=$heuresSPUpdate or $pUpdate == null) {
            $heuresSP=array();
            $p=new personnel();
            $p->fetch("nom");
            if (!empty($p->elements)) {
                // Pour chaque agents
                foreach ($p->elements as $key1 => $value1) {
                    $heuresSP[$key1]=$value1["heures_hebdo"];

                    if (strpos($value1["heures_hebdo"], "%")) {
                        $minutesHebdo=0;
                        if ($value1['temps']) {
                            $temps=json_decode(html_entity_decode($value1['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                            if (!is_array($temps)) {
                                $temps = array();
                            }

                            // Calcul des heures
                            // Pour chaque jour de la semaine
                            foreach ($dates as $key2 => $jour) {
                                // $pause = true si pause détectée le midi
                                $pause=false;
                                // Offset : pour semaines 1,2,3 ...
                                $offset=($semaine3*7)-7;
                                $key3=$key2+$offset;
                                // Si heure de début et de fin de matiné
                                if (array_key_exists($key3, $temps) and $temps[$key3][0] and $temps[$key3][1]) {
                                    $minutesHebdo+=diff_heures($temps[$key3][0], $temps[$key3][1], "minutes");
                                    $pause=true;
                                }
                                // Si heure de début et de fin d'après midi
                                if (array_key_exists($key3, $temps) and $temps[$key3][2] and $temps[$key3][3]) {
                                    $minutesHebdo+=diff_heures($temps[$key3][2], $temps[$key3][3], "minutes");
                                    $pause=true;
                                }
                                // Si pas de pause le midi
                                if (!$pause) {
                                    // Et heure de début et de fin de journée
                                    if (array_key_exists($key3, $temps) and $temps[$key3][0] and $temps[$key3][3]) {
                                        $minutesHebdo+=diff_heures($temps[$key3][0], $temps[$key3][3], "minutes");
                                    }
                                }
                            }
                        }

                        $heuresRelles=$minutesHebdo/60;
                        // On applique le pourcentage
                        $pourcent=(float) str_replace("%", null, $value1["heures_hebdo"]);
                        $heuresRelles=$heuresRelles*$pourcent/100;
                        $heuresSP[$key1]=$heuresRelles;
                    }
                }
                // Utilisateur "Tout le monde"
                $heuresSP[2]=0;
            }

            // Enregistrement des horaires dans la base de données
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("heures_sp", array("semaine"=>$j1));
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("heures_sp", array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heuresSP)));
        }
    }
    return (array) $heuresSP;
}

/** @function calculSiPresent
 *  @param string $debut : heure de début, format 00:00:00
 *  @param string $fin : heure de fin, format 00:00:00
 *  @param array $temps : tableau emploi du temps de l'agent
 *  @param int $jour : jour de la semaine de 0 à 6 puis de 7 à 13 en semaines paires/impaires, etc.
 *  La fonction retourne true si l'agent est disponible pendant toute, false s'il la plage est en dehors de ses horaires de travail ou s'il est en pause
 */
function calculSiPresent($debut, $fin, $temps, $jour)
{

    $wh = new WorkingHours($temps);
    $tab = $wh->hoursOf($jour);
  
    // Confrontation du créneau de service public aux tableaux
    foreach ($tab as $elem) {
        if (($elem[0] <= $debut) and ($elem[1] >= $fin)) {
            return true;
        }
    }
  
    return false;
}


/** @fonctions de comparaison
 */

function cmp_0($a, $b)
{
    $a[0] > $b[0];
}

function cmp_0desc($a, $b)
{
    return $a[0] < $b[0];
}

function cmp_01($a, $b)
{
    return $a[0][1] > $b[0][1];
}

function cmp_02($a, $b)
{
    return $a[0][2] > $b[0][2];
}

function cmp_03($a, $b)
{
    return $a[0][3] > $b[0][3];
}

function cmp_03desc($a, $b)
{
    return $a[0][3] < $b[0][3];
}

function cmp_1($a, $b)
{
    $a[1]=html_entity_decode($a[1], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b[1]=html_entity_decode($b[1], ENT_QUOTES|ENT_IGNORE, "utf-8");
    return strtolower($a[1]) > strtolower($b[1]);
}

function cmp_1desc($a, $b)
{
    return $a[1] < $b[1];
}

function cmp_2($a, $b)
{
    return $a[2] > $b[2];
}

function cmp_2desc($a, $b)
{
    return $a[2] < $b[2];
}

function cmp_heure($a, $b)
{
    return $a['heure'] > $b['heure'];
}

function cmp_jour($a, $b)
{
    return $a['jour'] > $b['jour'];
}

function cmp_nom($a, $b)
{
    $a['nom']=html_entity_decode($a['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['nom']=html_entity_decode($b['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    return strtolower($a['nom']) > strtolower($b['nom']);
}

function cmp_nom_prenom($a, $b)
{
    $a['nom']=html_entity_decode($a['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['nom']=html_entity_decode($b['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $a['prenom']=html_entity_decode($a['prenom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['prenom']=html_entity_decode($b['prenom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    if (strtolower($a['nom']) == strtolower($b['nom'])) {
        return strtolower($a['prenom']) > strtolower($b['prenom']);
    }
    return strtolower($a['nom']) > strtolower($b['nom']);
}

function cmp_nom_prenom_debut_fin($a, $b)
{
    $a['nom']=html_entity_decode($a['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['nom']=html_entity_decode($b['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $a['prenom']=html_entity_decode($a['prenom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['prenom']=html_entity_decode($b['prenom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    if (strtolower($a['nom']) == strtolower($b['nom'])) {
        if (strtolower($a['prenom']) == strtolower($b['prenom'])) {
            if (strtolower($a['debut']) == strtolower($b['debut'])) {
                return strtolower($a['fin']) > strtolower($b['fin']);
            }
            return strtolower($a['debut']) > strtolower($b['debut']);
        }
        return strtolower($a['prenom']) > strtolower($b['prenom']);
    }
    return strtolower($a['nom']) > strtolower($b['nom']);
}

function cmp_ordre($a, $b)
{
    return $a['ordre'] > $b['ordre'];
}

function cmp_perso_debut_fin($a, $b)
{
    if ($a['perso_id'] == $b['perso_id']) {
        if ($a['debut'] == $b['debut']) {
            return $a['fin'] > $b['fin'];
        }
        return $a['debut'] > $b['debut'];
    }
    return $a['perso_id'] > $b['perso_id'];
}

function cmp_prenom_nom($a, $b)
{
    $a['nom']=html_entity_decode($a['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['nom']=html_entity_decode($b['nom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $a['prenom']=html_entity_decode($a['prenom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b['prenom']=html_entity_decode($b['prenom'], ENT_QUOTES|ENT_IGNORE, "utf-8");
    if (strtolower($a['prenom']) == strtolower($b['prenom'])) {
        return strtolower($a['nom']) > strtolower($b['nom']);
    }
    return strtolower($a['prenom']) > strtolower($b['prenom']);
}

function cmp_debut_fin($a, $b)
{
    if ($a['debut'] == $b['debut']) {
        return $a['fin'] > $b['fin'];
    }
    return $a['debut'] > $b['debut'];
}

function cmp_debut_fin_nom($a, $b)
{
    if ($a['debut'] == $b['debut']) {
        if ($a['fin'] == $b['fin']) {
            return strtolower(html_entity_decode($a['nom'], ENT_QUOTES|ENT_IGNORE, "UTF-8")) > strtolower(html_entity_decode($b['nom'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
        }
        return $a['fin'] > $b['fin'];
    }
    return $a['debut'] > $b['debut'];
}

function cmp_semaine($a, $b)
{
    return $a['semaine'] > $b['semaine'];
}
    
function cmp_semainedesc($a, $b)
{
    $a['semaine'] < $b['semaine'];
}

function cmp_strip_tags($a, $b)
{
    $a = html_entity_decode(strip_tags($a), ENT_QUOTES|ENT_IGNORE, "utf-8");
    $b = html_entity_decode(strip_tags($b), ENT_QUOTES|ENT_IGNORE, "utf-8");
    return strtolower($a) > strtolower($b);
}

function createURL($page=null)
{
    // Construction d'une URL

    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $url="$protocol://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

    if (strstr($url, '/index.php')) {
        $pos = strpos($url, '/index.php');
        $url = substr($url, 0, $pos);
    }

    if (strstr($url, '/authentification.php')) {
        $pos = strpos($url, '/authentification.php');
        $url = substr($url, 0, $pos);
    }

    if ($page) {
        $url .= "/index.php?page=".$page;
    }

    return $url;
}


// Génération d'un CSRF Token
function CSRFToken()
{
    if (!empty($_SESSION['oups']['CSRFToken'])) {
        return $_SESSION['oups']['CSRFToken'];
    }
  
    // PHP 7
    if (phpversion() >= 7) {
        $CSRFToken = bin2hex(random_bytes(32));
    }

    // PHP 5.3+
    else {
        $CSRFToken = bin2hex(openssl_random_pseudo_bytes(32));
    }

    $_SESSION['oups']['CSRFToken'] = $CSRFToken;

    return $CSRFToken;
}

function date_time($date)
{
    if ($date=="0000-00-00 00:00:00") {
        return null;
    } else {
        $a=substr($date, 0, 4);
        $m=substr($date, 5, 2);
        $j=substr($date, 8, 2);
        $h=substr($date, 11, 2);
        $min=substr($date, 14, 2);
        $today=date("d/m/Y");
        if ($today=="$j/$m/$a") {
            $date="$h:$min";
        } else {
            $date="$j/$m/$a $h:$min";
        }
        return $date;
    }
}

function dateAlpha($date, $day=true, $year=true)
{
    if (!$date or $date=="0000-00-00") {
        return false;
    }

    $tmp=explode("-", $date);
    $dayOfMonth=($tmp[2]=="01")?"1<sup>er</sup>":intval($tmp[2]);
    switch ($tmp[1]) {
    case "01": $month="janvier" ; break;
    case "02": $month="février" ; break;
    case "03": $month="mars" ; break;
    case "04": $month="avril" ; break;
    case "05": $month="mai" ; break;
    case "06": $month="juin" ; break;
    case "07": $month="juillet" ; break;
    case "08": $month="août" ; break;
    case "09": $month="septembre" ; break;
    case "10": $month="octobre" ; break;
    case "11": $month="novembre" ; break;
    case "12": $month="décembre" ; break;
  }

    if ($day) {
        $day=date("w", mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]));
        switch ($day) {
      case 1: $day="Lundi"; break;
      case 2: $day="Mardi"; break;
      case 3: $day="Mercredi"; break;
      case 4: $day="Jeudi"; break;
      case 5: $day="Vendredi"; break;
      case 6: $day="Samedi"; break;
      case 0: $day="Dimanche"; break;
    }
        $return=$day." ".$dayOfMonth." ".$month;
        if ($year) {
            $return.=" ".$tmp[0];
        }
    } else {
        $return=$dayOfMonth." ".$month;
        if ($year) {
            $return.=" ".$tmp[0];
        }
    }
    return $return;
}

function dateAlpha2($date)
{
    $tmp=explode("-", $date);
    $dayOfMonth=($tmp[2]=="01")?"1er":intval($tmp[2]);
    switch ($tmp[1]) {
    case "01": $month="janvier" ; break;
    case "02": $month="février" ; break;
    case "03": $month="mars" ; break;
    case "04": $month="avril" ; break;
    case "05": $month="mai" ; break;
    case "06": $month="juin" ; break;
    case "07": $month="juillet" ; break;
    case "08": $month="août" ; break;
    case "09": $month="septembre" ; break;
    case "10": $month="octobre" ; break;
    case "11": $month="novembre" ; break;
    case "12": $month="décembre" ; break;
  }
    $day=date("w", mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]));
    switch ($day) {
    case 1: $day="Lundi"; break;
    case 2: $day="Mardi"; break;
    case 3: $day="Mercredi"; break;
    case 4: $day="Jeudi"; break;
    case 5: $day="Vendredi"; break;
    case 6: $day="Samedi"; break;
    case 0: $day="Dimanche"; break;
  }
    return $day."<br/>".$dayOfMonth." ".$month;
}

function dateFr($date, $heure=null)
{
    if ($date=="0000-00-00" or $date=="00/00/0000" or $date=="" or !$date) {
        return null;
    }
    if (substr($date, 4, 1)=="-") {
        $dateFr=substr($date, 8, 2)."/".substr($date, 5, 2)."/".substr($date, 0, 4);
        if ($heure and substr($date, 13, 1)==":" and substr($date, 11, 8)!="00:00:00" and substr($date, 11, 8)!="23:59:59") {
            $dateFr.=" ".substr($date, 11, 2)."h".substr($date, 14, 2);
        }
        return $dateFr;
    } else {
        $dateEn=substr($date, 6, 4)."-".substr($date, 3, 2)."-".substr($date, 0, 2);
        return $dateEn;
    }
}

function dateFr3($date)
{
    return preg_replace("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", "$3/$2/$1", $date);
}

function dateSQL($date)
{
    return preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $date);
}

function decode($n)
{
    if (is_array($n)) {
        return array_map("decode", $n);
    }
    return utf8_decode($n);
}

function decrypt($crypted_token)
{
    if ($crypted_token === null) {
        return null;
    }

    $key="AB0972FA445DDE66178ADF76";
    if (!empty($GLOBALS['config']['secret'])) {
        $key = $GLOBALS['config']['secret'];
    }

    $decrypted_token = false;

    if (preg_match("/^(.*)::(.*)$/", $crypted_token, $regs)) {
        // decrypt encrypted string
        list(, $crypted_token, $enc_iv) = $regs;
        $enc_method = 'AES-128-CTR';
        $enc_key = openssl_digest($key, 'SHA256', true);
        $decrypted_token = openssl_decrypt($crypted_token, $enc_method, $enc_key, 0, hex2bin($enc_iv));
        unset($crypted_token, $enc_method, $enc_key, $enc_iv, $regs);
    }
    return $decrypted_token;
}

function encrypt($string)
{
    if ($string === null) {
        return null;
    }

    $key="AB0972FA445DDE66178ADF76";
    if (!empty($GLOBALS['config']['secret'])) {
        $key = $GLOBALS['config']['secret'];
    }

    $enc_method = 'AES-128-CTR';
    $enc_key = openssl_digest($key, 'SHA256', true);
    $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($enc_method));
    $crypted_string = openssl_encrypt($string, $enc_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv);
    unset($string, $enc_method, $enc_key, $enc_iv);

    return $crypted_string;
}

function gen_trivial_password($len = 6)
{
    $r = '';
    for ($i=0; $i<$len; $i++) {
        $r .= chr(rand(0, 25) + ord('a'));
    }
    return $r;
}

// getJSFiles : permet de rechercher les scripts liés à la page courante (fichiers .js dans sous dossier js)
function getJSFiles($page, $version)
{
    if (!$page) {
        return false;
    }

    $pos=strpos(strrev($page), "/");
    $folder=substr($page, 0, -$pos);
    if ($folder and is_dir("{$folder}js")) {
        foreach (scandir("{$folder}js") as $elem) {
            if (substr($elem, -3)==".js") {
                echo "<script type='text/JavaScript' src='{$folder}js/{$elem}?version=$version'></script>\n";
            }
        }
    }
}

function heure2($heure)
{
    $heure=explode(":", $heure);
    if (!array_key_exists(1, $heure)) {
        return false;
    }

    $h=$heure[0];
    $m=$heure[1];
    $heure=$h."h".$m;
    return $heure;
}

function heure3($heure)
{
    $heure=str_replace(":", "h", $heure);
    $heure=substr($heure, 0, 5);
    if (substr($heure, 3, 2)=="00") {
        $heure=substr($heure, 0, 3);
    }
    if (substr($heure, 0, 1)=="0") {
        $heure=substr($heure, 1, strlen($heure));
    }
    return $heure;
}

function heure4($heure, $return0=false)
{
    if (!$heure and !$return0) {
        return null;
    }
    if (!$heure and $return0) {
        return "0h00";
    }
  
    if (stripos($heure, "h")) {
        $tmp = explode('h', $heure);
        $hre = $tmp[0];
        $min = $tmp[1];
        $centiemes = $min / 60 ;
        $hre += $centiemes;
        $heure = number_format($hre, 2, '.', '');
    } else {
        if (is_numeric($heure)) {
            $hre = floor($heure);
            $centiemes = $heure - $hre;
            $minutes = $centiemes * 0.6;
            if ($minutes >=0.595) {
                $hre++;
                $minutes = 0;
            }
            if ($hre >= 0) {
                $hre += $minutes;
            } else {
                $hre -= $minutes;
            }
            $heure = number_format($hre, 2, 'h', ' ');
        }
    }
    return $heure;
}

/** function heures
 * Reçoit une chaine contenant 2 heures au format 0h00, 00h00, 0h, 00h, séparées par un - et retourne un tableau contenant 2 chaines au format 00:00:00
 * Ex : 8h30-10h --> array('08:30:00','10:00:00')
 * @param string $h, 2 heures séparées par un -
 * @return array $tmp, tableau contenant les 2 heures formatées, retourne false si $h est vide après nettoyage
 */
function heures($h)
{
    $h = preg_replace('/[^0123456789hH-]/', '', $h);
    $h = str_replace(array('h','H'), ':', $h);
    if (empty($h)) {
        return false;
    }

    $tmp = explode('-', $h);
    $tmp0 = explode(':', $tmp[0]);
    $tmp1 = explode(':', $tmp[1]);
  
    $tmp[0] = sprintf("%02d:%02d:00", $tmp0[0], $tmp0[1]);
    $tmp[1] = sprintf("%02d:%02d:00", $tmp1[0], $tmp1[1]);
  
    return $tmp;
}

/**
 * html_entity_decode_latin1
 * Utiliée pour l'export des statistiques (statistiques/export.php)
 * Conversion des caractères HTML en iso-8859-1
 */
function html_entity_decode_latin1($n)
{
    if (is_array($n)) {
        return array_map("html_entity_decode_latin1", $n);
    }
    return html_entity_decode($n, ENT_QUOTES|ENT_IGNORE, "ISO-8859-1");
}


/**
 * Log le login tenté et l'adresse IP du client dans la table ip_blocker pour bloquer si trop d'échec
 * @param string $login : login saisi par l'utilisateur
 * @param int config IPBlocker-TimeChecked : période en minutes pendant laquelle on recherche les échecs
 * @param int config IPBlocker-Attempts : nombre d'échecs autorisés
 */
function loginFailed($login, $CSRFToken)
{
    // Recherche le nombre de login failed lors des $seconds dernières secondes
    $seconds=$GLOBALS['config']['IPBlocker-TimeChecked']*60;
    $attempts=$GLOBALS['config']['IPBlocker-Attempts'];

    $timestamp=date("Y-m-d H:i:s", strtotime(" -$seconds seconds"));
    $db=new db();
    $db->select2("ip_blocker", null, array("ip"=>$_SERVER['REMOTE_ADDR'], "status"=>"failed", "timestamp"=> ">=$timestamp"));
    // S'il y a eu $attempts -1 echecs lors des $seconds dernières secondes, on block l'accès
    $status=$db->nb>=$attempts?"blocked":"failed";

    // Insertion dans la base de données
    $insert=array("ip"=>$_SERVER['REMOTE_ADDR'], "login"=>$login, "status"=>$status);
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("ip_blocker", $insert);
}

/**
 * Retourne le nombre de secondes restantes avant que l'IP bloquée soit de nouveau autorisée à se connecter
 * @param int config IPBlocker-Wait : temps de blocages des IP en minutes
 */
function loginFailedWait()
{
    $seconds=$GLOBALS['config']['IPBlocker-Wait']*60;
    $wait=0;

    $db=new db();
    $db->select2("ip_blocker", "timestamp", array("ip"=>$_SERVER['REMOTE_ADDR'], "status"=>"blocked"), "ORDER BY `timestamp` DESC LIMIT 0,1");

    if ($db->result) {
        $timestamp=$db->result[0]['timestamp'];
        $wait=strtotime($timestamp) + (int) $seconds - time();
    }
    return $wait;
}

/**
 * Log le login et l'adresse IP du client dans la table ip_blocker pour informations
 * @param string $login : login saisi par l'utilisateur
 */
function loginSuccess($login, $CSRFToken)
{
    $insert=array("ip"=>$_SERVER['REMOTE_ADDR'], "login"=>$login, "status"=>"success");
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("ip_blocker", $insert);
}

function logs($msg, $program=null, $CSRFToken)
{
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("log", array("msg"=>$msg,"program"=>$program));
}


/**
 * Fonction nom
 * Retourne le nom de l'agent dont l'id est donné en argument
 * @param  int $id : id de l'agent
 * @param string $format: format de la chaîne retournée (ex: nom p)
 * @param array $agents : liste de tous les agents (permet de réduire le nombre de requêtes SQL et la latence si la fonction nom est utilisée dans une boucle
 */
function nom($id, $format="nom p", $agents=array())
{

  // id 99999 == cron (tâche planifiée)
    if ($id == 99999) {
        return null;
    }
  
    if (empty($agents)) {
        $db=new db();
        $db->query("select nom,prenom from {$GLOBALS['config']['dbprefix']}personnel where id=$id;");
        $nom=$db->result[0]['nom'];
        $prenom=$db->result[0]['prenom'];
    } else {
        if (!isset($agents[$id])) {
            return 'error';
        }
        $nom=$agents[$id]['nom'];
        $prenom=$agents[$id]['prenom'];
    }
  
    switch ($format) {
    case "nom prenom": $nom="$nom $prenom";	break;
    case "prenom nom": $nom="$prenom $nom";	break;
    default: $nom="$nom ".substr($prenom, 0, 1);	break;
  }
    return $nom;
}

function pl_stristr($haystack, $needle)
{
    if (stristr(removeAccents($haystack), removeAccents(trim($needle)))) {
        return true;
    }
    return false;
}

function removeAccents($string)
{
    if (is_array($string)) {
        return array_map("removeAccents", $string);
    }
    $string=html_entity_decode($string, ENT_QUOTES|ENT_IGNORE, "UTF-8");
    $pairs=array("À"=>"A","Á"=>"A","Â"=>"A","Ã"=>"A","Ä"=>"A","Å"=>"A","à"=>"a","á"=>"a","â"=>"a",
    "ã"=>"a","ä"=>"a","å"=>"a","Ò"=>"O","Ó"=>"O","Ô"=>"O","Õ"=>"O","Õ"=>"O","Ö"=>"O","Ø"=>"O",
    "ò"=>"o","ó"=>"o","ô"=>"o","õ"=>"o","ö"=>"o","ø"=>"o","È"=>"E","É"=>"E","Ê"=>"E","Ë"=>"E",
    "è"=>"e","é"=>"e","ê"=>"e","ë"=>"e","ð"=>"e","Ç"=>"C","ç"=>"c","Ð"=>"d","Ì"=>"I","Í"=>"I",
    "Î"=>"I","Ï"=>"I","ì"=>"i","í"=>"i","î"=>"i","ï"=>"i","Ù"=>"U","Ú"=>"U","Û"=>"U","Ü"=>"U",
    "ù"=>"u","ú"=>"u","û"=>"u","ü"=>"u","Ñ"=>"N","ñ"=>"n","ÿ"=>"y","ý"=>"y","ŷ"=>"y","ỳ"=>"y",
    "Ÿ"=>"Y","Ỳ"=>"Y","Ŷ"=>"Y","'"=>"_");
    $string=strtr($string, $pairs);
    return htmlentities($string, ENT_QUOTES|ENT_IGNORE, "UTF-8");
}

function recurrenceRRuleText($rrule)
{
    $freq       = preg_replace('/.*FREQ=(\w*).*/', "$1", $rrule);
    $interval   = strpos($rrule, 'INTERVAL')      ? preg_replace('/.*INTERVAL=(\d*).*/', "$1", $rrule)      : 1;
    $count      = strpos($rrule, 'COUNT')         ? preg_replace('/.*COUNT=(\d*).*/', "$1", $rrule)         : null;
    $until      = strpos($rrule, 'UNTIL')         ? preg_replace('/.*UNTIL=(\w*).*/', "$1", $rrule)         : null;
    $byday      = strpos($rrule, 'BYDAY')         ? preg_replace('/.*BYDAY=([0-9A-Z-,]*).*/', "$1", $rrule) : null;
    $bymonthday = strpos($rrule, 'BYMONTHDAY')    ? preg_replace('/.*BYMONTHDAY=(\d*).*/', "$1", $rrule)    : null;

    // UNTIL : Conversion au format d/m/Y sur le fuseau horaire local
    if ($until) {
        $date = new DateTime($until, new DateTimeZone('GMT'));
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
        $until = $date->format('d/m/Y');
    }

    switch ($freq) {
    case 'DAILY':
      if ($interval == 1 or $interval == null) {
          $text = 'Tous les jours';
      } else {
          $text = "Tous les $interval jours";
      }
      break;

    case 'WEEKLY':
      if ($interval == 1 or $interval == null) {
          $text = 'Chaque semaine';
      } else {
          $text = "Toutes les $interval semaines";
      }

      if ($byday) {
          $days = str_replace(array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'), array(' lundis', ' mardis', ' mercredis', ' jeudis', ' vendredis', ' samedis', ' dimanches'), $byday);
          $days = preg_replace('/(.*),(.[^,]*)$/', "$1 et $2", $days);
          $text .= ", les$days";
      }
      break;

    case 'MONTHLY':
      if ($interval == 1 or $interval == null) {
          $text = 'Tous les mois';
      } else {
          $text = "Tous les $interval mois";
      }

      if ($byday) {
          if (substr($byday, 0, 2) == '-1') {
              $n = 'Le dernier ';
              $d = substr($byday, 2);
          } else {
              $n = substr($byday, 0, 1);
              $d = substr($byday, 1);
              $n = $n == 1 ? 'Le 1<sup>er</sup> ' : "Le $n<sup>&egrave;me</sup> ";
          }
          $day = str_replace(array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'), array(' lundi', ' mardi', ' mercredi', ' jeudi', ' vendredi', ' samedi', ' dimanche'), $d);

          $text = $text == 'Tous les mois' ? $n.$day.' de chaque mois' : $n.$day.', tous les '.$interval.' mois';
      }

      if ($bymonthday) {
          $n = $bymonthday;
          $n = $n == 1 ? 'Le 1<sup>er</sup>' : 'Le '+$n;
          $text = $text == 'Tous les mois' ? "$n de chaque mois" : "$n, tous les $interval mois";
      }
      break;
  }

    if ($until) {
        $text .= " jusqu'au $until";
    } elseif ($count) {
        $text .= ", $count fois";
    }
    return $text;
}

/**
 * Fonction selectHeure
 * Utilisée pour afficher les menus déroulants des heures pour les absences, congés, éditions des tableaux.
 * @param int $min : heure de début (de 0 à 22)
 * @param int $max : heure de fin (de 1 à 23)
 * @param boolean $blank : afficher ou non une première option vide
 * @param string $selectedValue : valeur du champ si renseignée
 */
function selectHeure($min, $max, $blank=false, $selectedValue=null)
{
    $granularite = $GLOBALS['config']['Granularite'];

    if ($blank) {
        echo "<option value=''>&nbsp;</option>\n";
    }

    for ($i=$min;$i<$max+1;$i++) {
        $hre = sprintf("%'.02d", $i);

        for ($j=0; $j<60; $j=$j+$granularite) {
            $min = sprintf("%'.02d", $j);
            $selected=$selectedValue == "$hre:$min:00"?"selected='selected'":null;
            echo "<option value='$hre:$min:00' $selected>{$hre}h$min</option>\n";
        }
    }
}

/**
 * Fonction selectTemps
 * Utilisée pour afficher les menus déroulants des heures pour remplir les plannings de présences
 * dans les fiches agents et dans le module Planning Hebdo
 * @param int $jour : jour de la semaine sur 1 à 3 semaines (de 0 à 21)
 * @param int $i : position du menu déroulant sur la ligne et période de la journée (Arrivée, début pause, fin pause, Départ), de 0 à 3
 * @param boolean $periodes : précise si on utilise ou pas les période prédéfinies. Plus supporté ($periodes = false ou null).
 * @param string $class : permet d'attribuer une class CSS au menu pour personnaliser l'affichage ou permettre des sélections JQuery
 */
function selectTemps($jour, $i, $periodes=null, $class=null)
{
    $granularite = $GLOBALS['config']['Granularite'];
    $temps=null;
  
    $class=$class?"class='$class'":null;
    if (array_key_exists("temps", $GLOBALS)) {
        $temps=$GLOBALS['temps'];
    }
    if ($periodes) {
        $select="<select name='temps{$periodes}[$jour][$i]' $class>\n";
    } else {
        $select="<select name='temps[$jour][$i]' $class>\n";
    }
    $select.="<option value=''>&nbsp;</option>\n";

    for ($j=7;$j<=23;$j++) {
        $hre = sprintf("%'.02d", $j);
    
        for ($k=0; $k<60; $k=$k+$granularite) {
            $min = sprintf("%'.02d", $k);
      
            $selected = null;
            if (isset($temps[$jour][$i])) {
                $selected = $temps[$jour][$i] == "$hre:$min:00" ? "selected='selected'" : null;
            }
      
            $select.="<option value='$hre:$min:00' $selected >"."{$hre}h{$min}</option>\n";
        }
    }
  
    $select.="</select>\n";
    return $select;
}

function verifmail($texte)
{
    return preg_match("/^[^@ ]+@[^@ ]+\.[^@ \.]+$/", $texte);
}
