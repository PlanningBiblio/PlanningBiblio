<?php
/**
Planning Biblio, Version 2.8.05
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/class.planningHebdo.php
Création : 23 juillet 2013
Dernière modification : 6 décembre 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant le fonctions planningHebdo.
Appelé par les autres fichiers du dossier planningHebdo
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé

require_once __DIR__."/../absences/class.absences.php";

class planningHebdo
{
    public $agent=null;
    public $config=array();
    public $CSRFToken=null;
    public $dates=array();
    public $debut=null;
    public $elements=array();
    public $error=null;
    public $fin=null;
    public $id=null;
    public $ignoreActuels=null;
    public $periodes=null;
    public $perso_id=null;
    public $perso_ids=null;
    public $tri=null;
    public $valide=null;
    public $merge_exception = true;


    public function __construct()
    {
    }

    public function add($data)
    {
        // Modification du format des dates de début et de fin si elles sont en français
        if (array_key_exists("debut", $data)) {
            $data['debut']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $data['debut']);
            $data['fin']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $data['fin']);
        }
        $data['breaktime'] = isset($data['breaktime']) ? $data['breaktime'] : null;
        $data['exception'] = $data['exception'] ?? 0;

        $perso_id=array_key_exists("perso_id", $data)?$data["perso_id"]:$_SESSION['login_id'];

        // Validation
        // Par défaut = 0, si $data['validation'] valide = login de l'agent logué, validation = date courante

        $valide_n1 = 0;
        $validation_n1 = "0000-00-00 00:00:00";
        $valide_n2 = 0;
        $validation_n2 = "0000-00-00 00:00:00";

        if (array_key_exists("validation", $data)) {
            switch ($data['validation']) {
        case -1:
          $valide_n1 = -1 * $_SESSION['login_id'];
          $validation_n1 = date("Y-m-d H:i:s");
          $valide_n2 = 0;
          $validation_n2 = "0000-00-00 00:00:00";
          break;
        case 1:
          $valide_n1 = $_SESSION['login_id'];
          $validation_n1 = date("Y-m-d H:i:s");
          $valide_n2 = 0;
          $validation_n2 = "0000-00-00 00:00:00";
          break;
        case -2:
          $valide_n2 = -1 * $_SESSION['login_id'];
          $validation_n2 = date("Y-m-d H:i:s");
          break;
        case 2:
          $valide_n2 = $_SESSION['login_id'];
          $validation_n2 = date("Y-m-d H:i:s");
          break;
      }
        }

        $CSRFToken=$data['CSRFToken'];
        unset($data['CSRFToken']);

        // Si $data['annee'] : il y a 2 périodes distinctes avec des horaires définis
        // (horaires normaux et horaires réduits) soit 2 tableaux à insérer
        if (array_key_exists("annee", $data)) {
            // Récupération des horaires
            $this->dates=array($data['annee']);
            $this->getPeriodes();
            $dates=$this->periodes;

            // 1er tableau
            $insert = array(
                'perso_id'      => $perso_id,
                'debut'         => $dates[0][0],
                'fin'           => $dates[0][1],
                'temps'         => json_encode($data['temps']),
                'valide_n1'     => $valide_n1,
                'validation_n1' => $validation_n1,
                'valide'        => $valide_n2,
                'validation'    => $validation_n2,
                'breaktime'     => json_encode($data['breaktime']),
                'exception'     => $data['exception']
            );

            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("planning_hebdo", $insert);
            $this->error=$db->error;

            // 2ème tableau
            $insert = array(
                "perso_id"      => $perso_id,
                "debut"         => $dates[0][2],
                "fin"           => $dates[0][3],
                "temps"         => json_encode($data['temps2']),
                "valide_n1"     => $valide_n1,
                "validation_n1" => $validation_n1,
                "valide"        => $valide_n2,
                "validation"    => $validation_n2,
                'breaktime'     => json_encode($data['breaktime']),
                'exception'     => $data['exception']
            );

            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("planning_hebdo", $insert);
            $this->error=$db->error?$db->error:$this->error;
        }

        // Sinon, insertion d'un seul tableau
        else {
            $insert = array(
                'perso_id'      => $perso_id,
                'debut'         => $data['debut'],
                'fin'           => $data['fin'],
                'temps'         => json_encode($data['temps']),
                'valide_n1'     => $valide_n1,
                'validation_n1' => $validation_n1,
                'valide'        => $valide_n2,
                'validation'    => $validation_n2,
                'breaktime'     => json_encode($data['breaktime']),
                'exception'     => $data['exception'],
                'nb_semaine'    => $data['number_of_weeks'],
            );

            // Dans le cas d'une copie (voir fonction copy)
            if (isset($data['remplace'])) {
                $insert['remplace']=$data['remplace'];
            }

            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("planning_hebdo", $insert);
            $this->error=$db->error;
        }

        if ($GLOBALS['config']['PlanningHebdo-notifications-agent-par-agent']) {
            $a = new absences();
            $a->getRecipients2(null, $perso_id, 1, 1200);
            $destinataires = $a->recipients;
        } else {
            $this->getRecipients(1, $perso_id);
            $destinataires = $this->recipients;
        }

        if (!empty($destinataires)) {
            $nomAgent = nom($perso_id, "prenom nom");
            $sujet="Nouveau planning de présence, ".html_entity_decode($nomAgent, ENT_QUOTES|ENT_IGNORE, "UTF-8");
            $message=$nomAgent;
            $message.=" a enregistré un nouveau planning de présence dans l'application Planning Biblio<br/>";
            $message.="Rendez-vous dans le menu administration / Plannings de présence de votre application Planning Biblio pour le valider.";

            // Envoi du mail
            $m=new CJMail();
            $m->subject=$sujet;
            $m->message=$message;
            $m->to=$destinataires;
            $m->send();
        }
    }

    public function copy($data)
    {
        // Modification du format des dates de début et de fin si elles sont en français
        $data['debut']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $data['debut']);
        $data['fin']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $data['fin']);

        $this->id=$data['id'];
        $this->fetch();
        $actuel=$this->elements[0];

        // Check if something changed, if not: return
        $compare_temps = strcmp(json_encode($actuel['temps']), json_encode($data['temps']));
        $compare_dates = ($data['debut'] != $actuel['debut'] or $data['fin'] != $actuel['fin']);
        $compare_perso_id = $data['perso_id'] != $actuel['perso_id'];
        
        // If nothing changed: return
        if (!$compare_temps and !$compare_dates and !$compare_perso_id) {
            return false;
        }

        // Copie de l'ancien planning avec modification des dates de début et/ou de fin
        $pl=array();

        // If dates didn't change, don't create multiple copies (only one copy : end of this method)
        if ($data['debut'] != $actuel['debut'] or $data['fin'] != $actuel['fin']) {
            // Copie de l'ancien planning
            $pl[0]=$actuel;
            $pl[0]['remplace']=$actuel['id'];
        }

        // Modification de la date de fin de la copie et création d'une 2ème copie si les 2 dates sont modifiées
        if ($data['debut']>$actuel['debut'] and $data['fin']<$actuel['fin']) {
            $pl[0]['fin']=date("Y-m-d", strtotime("-1 Day", strtotime($data['debut'])));
            $pl[1]=$actuel;
            $pl[1]['debut']=date("Y-m-d", strtotime("+1 Day", strtotime($data['fin'])));
            $pl[1]['remplace']=$actuel['id'];
        }
        // Modification de la date de fin de la copie si la date de début est modifiée
        elseif ($data['debut']>$actuel['debut']) {
            $pl[0]['fin']=date("Y-m-d", strtotime("-1 Day", strtotime($data['debut'])));
        }
        // Modification de la date de début de la copie si la date de fin est modifiée
        elseif ($data['fin']<$actuel['fin']) {
            $pl[0]['debut']=date("Y-m-d", strtotime("+1 Day", strtotime($data['fin'])));
        }

        // Enregistrement des copies
        foreach ($pl as $elem) {
            $elem['CSRFToken'] = $data['CSRFToken'];
            $p=new planningHebdo();
      
            $p->add($elem);
        }
    
        // Enregistrement du nouveau planning
        $data['remplace']=$actuel['id'];
        $data['validation'] = 0;
        $p=new planningHebdo();
        $p->add($data);
    }
  
    public function fetch()
    {
        // Recherche des services
        $p=new personnel();
        $p->fetch();
        foreach ($p->elements as $elem) {
            $services[$elem['id']]=$elem['service'];
        }

        // Filtre de recherche
        $filter="1";

        // Perso_id
        if ($this->perso_id) {
            $filter.=" AND `perso_id`='{$this->perso_id}'";
        }

        // Date, debut, fin
        $debut=$this->debut;
        $fin=$this->fin;
        $date=date("Y-m-d");
        if ($debut) {
            $fin=$fin?$fin:$date;
            $filter.=" AND `debut`<='$fin' AND `fin`>='$debut'";
        } else {
            $filter.=" AND `fin`>='$date'";
        }


        // Recherche des agents actifs seulement
        $perso_ids=array(0);
        $p=new personnel();
        $p->fetch("nom");
        foreach ($p->elements as $elem) {
            $perso_ids[]=$elem['id'];
        }

        // Recherche avec le nom de l'agent
        if ($this->agent) {
            $perso_ids=array(0);
            $p=new personnel();
            $p->fetch("nom", null, $this->agent);
            foreach ($p->elements as $elem) {
                $perso_ids[]=$elem['id'];
            }
        }

        if (!empty($this->perso_ids)) {
            $perso_ids = $this->perso_ids;
        }

        // Filtre pour agents actifs seulement et recherche avec nom de l'agent
        $perso_ids=join(",", $perso_ids);
        $filter.=" AND `perso_id` IN ($perso_ids)";

        // Valide
        if ($this->valide) {
            $filter.=" AND `valide`<>0";
        }
  
        // Ignore actuels (pour l'import)
        if ($this->ignoreActuels) {
            $filter.=" AND `actuel`=0";
        }
  
        // Filtre avec ID, si ID, les autres filtres sont effacés
        if ($this->id) {
            $filter="`id`='{$this->id}'";
        }

        $db=new db();
        $db->select("planning_hebdo", "*", $filter, "ORDER BY debut,fin,saisie");
    
        $p=new personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents = $p->elements;
    

        if ($db->result) {
            foreach ($db->result as $elem) {
                $elem['temps'] = json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                $elem['breaktime'] = json_decode(html_entity_decode($elem['breaktime'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                $elem['nom'] = nom($elem['perso_id'], 'nom p', $agents);
                $elem['service']=$services[$elem['perso_id']];
                $this->elements[]=$elem;
            }
        }

        // Tri par date de début, fin et nom des agents
        usort($this->elements, "cmp_debut_fin_nom");

        // Classe les plannings copiés (remplaçant) après les plannings d'origine
        $tab=array();
        foreach ($this->elements as $elem) {
            if (!$elem['remplace']) {
                $tab[]=$elem;
                foreach ($this->elements as $elem2) {
                    if ($elem2['remplace']==$elem['id']) {
                        $tab[]=$elem2;
                    }
                }
            }
        }

        // Merge exception planning into their target.
        if ($this->merge_exception) {
            foreach ($tab as $elem) {
                if ($target = $elem['exception']) {
                    // Searching for target planning.
                    foreach ($tab as $index => $elem2) {
                        if ($elem2['id'] == $target) {
                            $merged = $this->merge($elem, $elem2);
                            $tab[$index] = $merged;
                        }
                    }
                }
            }

            // Clear from exception.
            // Need to do that after last loop
            // to keep proper indexes.
            foreach ($tab as $index => $elem) {
                if ($target = $elem['exception']) {
                    unset($tab[$index]);
                }
            }
        }

        // $tab est vide si on accède directement à un planning copié,
        // on remplace donc $this->elements par $tab seulement si $tab n'est pas vide.
        if (!empty($tab)) {
            $this->elements=$tab;
        }
    }

    private function merge($from, $to)
    {
        $start_exception = $from['debut'];
        $end_exception = $from['fin'];

        $d = new datePl($start_exception);
        foreach ($d->dates as $pl_index => $date ) {
            if ($date >= $start_exception
                && $date <= $end_exception) {
                $to['temps'][$pl_index] = $from['temps'][$pl_index] ?? null;
            }
        }

        return $to;
    }

    public function getPeriodes()
    {
        if (!empty($this->dates)) {
            $dates=array();
            $annees=$this->dates;
            sort($annees);
            $i=0;
            foreach ($annees as $annee) {
                $db=new db();
                $db->select("planning_hebdo_periodes", "*", "`annee`='$annee'", "ORDER BY `annee`");
                if ($db->result) {
                    $dates[$i]=json_decode(html_entity_decode($db->result[0]['dates'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    $datesFr[$i]=array_map("dateFr", $dates[$i]);
                    $i++;
                } else {
                    $dates[$i]=null;
                    $datesFr[$i]=null;
                    $i++;
                }
            }
        }
        $this->periodes=$dates;
        $this->periodesFr=$datesFr;
    }

    /**
     * @function getRecipients
     * Retourne la liste des destinataires des notifications en fonction du niveau de validation.
     * @param int $validation = niveau de validation (int) :
     * - 1 : enregistrement d'une nouvelle absences
     * - 2 : modification d'une absence sans validation ou suppression
     * - 3 : validation N1
     * - 4 : validation N2
     * @param int $perso_id = ID de l'agent concerné par le planning de présence
     */
    public function getRecipients($validation, $perso_id)
    {
        $categories=$GLOBALS['config']["PlanningHebdo-notifications{$validation}"];
        $categories=json_decode(html_entity_decode(stripslashes($categories), ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        /*
        $categories : Catégories de personnes à qui les notifications doivent être envoyées
          tableau json issu de la config. : champ PlanningHebdo-notifications, PlanningHebdo-notifications2,
          PlanningHebdo-notifications3, PlanningHebdo-notifications4, en fonction du niveau de validation ($validation)
          Valeurs du tableau :
            0 : agents ayant le droits de validation les planning de présence au niveau 1
            1 : agents ayant le droits de validation les planning de présence au niveau 2
            2 : responsables directs (mails enregistrés dans la fiche des agents)
            3 : cellule planning (mails enregistrés dans la config.)
            4 : l'agent
        */

        // Informations relatives à l'agent : son mail, le mail de ses responsables
        $p = new personnel();
        $p->fetchById($perso_id);
        if (!empty($p->elements)) {
            $mail = $p->elements[0]['mail'];
            $mails_responsables = $p->elements[0]['mails_responsables'];
        }

        // recipients : liste des mails qui sera retournée
        $recipients=array();

        // Agents ayant le droits de gérer les plannings de présence au niveau 1
        if (in_array(0, $categories)) {
            $responsablesN1 = array();
            $db = new db();
            $db->select2('personnel', array('id', 'mail'), array('droits' => 'LIKE%1101%'));
            if ($db->result) {
                $responsablesN1 = $db->result;
            }

            foreach ($responsablesN1 as $elem) {
                if (!in_array(trim(html_entity_decode($elem['mail'], ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                    $recipients[]=trim(html_entity_decode($elem['mail'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                }
            }
        }

        // Agents ayant le droits de gérer les plannings de présence au niveau 1
        if (in_array(1, $categories)) {
            $responsablesN2 = array();
            $db = new db();
            $db->select2('personnel', array('id', 'mail'), array('droits' => 'LIKE%1201%'));
            if ($db->result) {
                $responsablesN2 = $db->result;
            }

            foreach ($responsablesN2 as $elem) {
                if (!in_array(trim(html_entity_decode($elem['mail'], ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                    $recipients[]=trim(html_entity_decode($elem['mail'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                }
            }
        }

        // Responsables directs
        if (in_array(2, $categories)) {
            if (is_array($mails_responsables)) {
                foreach ($mails_responsables as $elem) {
                    if (!in_array(trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                        $recipients[]=trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                    }
                }
            }
        }

        // Cellule planning
        if (in_array(3, $categories)) {
            $mailsCellule=explode(";", trim($GLOBALS['config']['Mail-Planning']));
            if (is_array($mailsCellule)) {
                foreach ($mailsCellule as $elem) {
                    if (!in_array(trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                        $recipients[]=trim(html_entity_decode($elem, ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                    }
                }
            }
        }

        // L'agent
        if (in_array(4, $categories)) {
            if (!in_array(trim(html_entity_decode($mail, ENT_QUOTES|ENT_IGNORE, "UTF-8")), $recipients)) {
                $recipients[]=trim(html_entity_decode($mail, ENT_QUOTES|ENT_IGNORE, "UTF-8"));
            }
        }

        $this->recipients=$recipients;
    }


    public function update($data)
    {
        // Modification du format des dates de début et de fin si elles sont en français
        $data['debut']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $data['debut']);
        $data['fin']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", "$3-$2-$1", $data['fin']);
        $data['breaktime'] = isset($data['breaktime']) ? $data['breaktime'] : null;

        $perso_id = !empty($data['valide']) ? $data['valide'] : $_SESSION['login_id'];

        // Validation : initialisation
        $valide_n1 = 0;
        $validation_n1 = "0000-00-00 00:00:00";
        $valide_n2 = 0;
        $validation_n2 = "0000-00-00 00:00:00";
        $notification = 2;

        // Validation
        if (!empty($data['validation'])) {
            switch ($data['validation']) {
                case 0:
                $valide_n1 = 0;
                $validation_n1 = "0000-00-00 00:00:00";
                $valide_n2 = 0;
                $validation_n2 = "0000-00-00 00:00:00";
                $notification = 2;
                break;
                case -1:
                $valide_n1 = -1 * $_SESSION['login_id'];
                $validation_n1 = date("Y-m-d H:i:s");
                $valide_n2 = 0;
                $validation_n2 = "0000-00-00 00:00:00";
                $notification = 3;
                break;
                case 1:
                $valide_n1 = $_SESSION['login_id'];
                $validation_n1 = date("Y-m-d H:i:s");
                $valide_n2 = 0;
                $validation_n2 = "0000-00-00 00:00:00";
                $notification = 3;
                break;
                case -2:
                $valide_n2 = -1 * $_SESSION['login_id'];
                $validation_n2 = date("Y-m-d H:i:s");
                $notification = 4;
                break;
                case 2:
                $valide_n2 = $_SESSION['login_id'];
                $validation_n2 = date("Y-m-d H:i:s");
                $notification = 4;
                break;
            }
        }

        $temps = json_encode($data['temps']);
        $breaktime = json_encode($data['breaktime']);
        $update = array(
            'debut'         => $data['debut'],
            'fin'           => $data['fin'],
            'temps'         => $temps,
            'modif'         => $perso_id,
            'modification'  => date("Y-m-d H:i:s"),
            'valide'        => $valide_n2,
            'validation'    => $validation_n2,
            'breaktime'     => $breaktime,
            'exception'     => $data['exception'],
            'nb_semaine'    => $data['number_of_weeks'],
        );

        if (isset($valide_n1)) {
            $update['valide_n1'] = $valide_n1;
            $update['validation_n1'] = $validation_n1;
        }

        $CSRFToken=$data['CSRFToken'];
        unset($data['CSRFToken']);

        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("planning_hebdo", $update, array("id"=>$data['id']));
        $this->error=$db->error;

        // Remplacement du planning de la fiche agent si validation et date courante entre debut et fin
        if ($valide_n2 > 0 and $data['debut']<=date("Y-m-d") and $data['fin']>=date("Y-m-d")) {
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('actuel'=>0), array('perso_id'=>$data['perso_id']));
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('actuel'=>1), array('id'=>$data['id']));
        }

        // Si validation d'un planning de remplacement, suppression du planning d'origine
        if ($valide_n2 > 0  and $data['remplace']) {
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->delete('planning_hebdo', array('id' =>$data['remplace']));
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('remplace'=>0), array('remplace'=>$data['remplace']));
        }

        // Envoi de la notification

        if ($GLOBALS['config']['PlanningHebdo-notifications-agent-par-agent']) {
            $a = new absences();
            $a->getRecipients2(null, $data['perso_id'], $notification, 1200);
            $destinataires = $a->recipients;
        } else {
            $this->getRecipients($notification, $data['perso_id']);
            $destinataires = $this->recipients;
        }

        $nomAgent = nom($data['perso_id'], "prenom nom");

        if (!empty($destinataires)) {
            if ($valide_n2 > 0) {
                $sujet="Validation d'un planning de présence, ".html_entity_decode($nomAgent, ENT_QUOTES|ENT_IGNORE, "UTF-8");
                $message="Un planning de présence de ";
                $message.=$nomAgent;
                $message.=" a été validé dans l'application Planning Biblio<br/>";
            } else {
                $sujet="Modification d'un planning de présence, ".html_entity_decode($nomAgent, ENT_QUOTES|ENT_IGNORE, "UTF-8");
                $message="Un planning de présence de ";
                $message.=$nomAgent;
                $message.=" a été modifié dans l'application Planning Biblio<br/>";
            }

            // Envoi du mail
            $m=new CJMail();
            $m->subject=$sujet;
            $m->message=$message;
            $m->to=$destinataires;
            $m->send();
        }
    }

    public function update_time()
    {
        $db=new db();
        $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['config']['dbprefix']}planning_hebdo';");
        $result = isset($db->result[0]['Update_time']) ? $db->result[0]['Update_time'] : null;
        return $result;
    }
  
    public function updatePeriodes($data)
    {
        $annee=array($data['annee'][0],$data['annee'][1]);
        // Convertion des dates JJ/MM/AAAA => AAAA-MM-JJ
        $data['dates'][0]=array_map("dateFr", $data['dates'][0]);
        $data['dates'][1]=array_map("dateFr", $data['dates'][1]);
        $dates=array(json_encode($data['dates'][0]),json_encode($data['dates'][1]));
    
        $CSRFToken=$data['CSRFToken'];
    
        for ($i=0;$i<count($annee);$i++) {
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->delete('planning_hebdo_periodes', array('annee'=>$annee[$i]));
            $this->error=$db->error?true:false;
            $insert=array("annee"=>$annee[$i],"dates"=>$dates[$i]);
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("planning_hebdo_periodes", $insert);
            $this->error=$db->error?true:$this->error;
        }
    }
}
