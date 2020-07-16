<?php
/**
Planning Biblio, Version 2.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : personnel/class.personnel.php
Création : 16 janvier 2013
Dernière modification : 24 mai 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe personnel : contient la fonction personnel::fetch permettant de rechercher les agents.
personnel::fetch prend en paramètres $tri (nom de la colonne), $actif (string), $name (string, nom ou prenom de l'agent)

Page appelée par les autres fichiers du dossier personnel
*/

class personnel
{
    public $elements=array();
    // supprime : permet de sélectionner les agents selon leur état de suppression
    // Tableau, valeur 0=pas supprimé, 1=1ère suppression (corbeille), 2=suppression définitive
    public $supprime=array(0);
  
    public $CSRFToken = null;

    public $offset = 0;

    public $responsablesParAgent = null;

    public function __construct()
    {
    }

    public function delete($liste)
    {
        // Suppresion des informations de la table personnel
        // NB : les entrées ne sont pas complétement supprimées car nous devons les garder pour l'historique des plannings et les statistiques. Mais les données personnelles sont anonymisées.
        $update=array("supprime" => "2", "login" => "CONCAT('deleted_',id)", "mail" => null, "arrivee" => null, "depart" => null, "postes" => null, "droits" => null, "password" => null,
            "commentaires" => "Suppression définitive le ".date("d/m/Y"), "last_login" => null, "temps" => null, "informations" => null, "recup" => null, "heures_travail" => null,
            "heures_hebdo" => null, "sites" => null, "mails_responsables" => null, "matricule" => null, "code_ics" => null, "url_ics" => null, "check_ics" => null, "check_hamac" => null,
            "conges_credit" => null, "conges_reliquat" => null, "conges_anticipation" => null, "conges_annuel" => null, "comp_time" => null, "nom" => "CONCAT('Agent_',id)", "prenom" => null);

        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("personnel", $update, "`id` IN ($liste)");

        // Suppression des informations sur les absences
        // NB : les entrées ne sont pas complétement supprimées car nous devons les garder pour l'historique des plannings et les statistiques. Mais les données personnelles sont anonymisées.
        $update = array('commentaires' => null, 'motif_autre' => null);

        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update('absences', $update, "`perso_id` IN ($liste)");

        // Suppression des informations sur les congés
        // NB : les entrées ne sont pas complétement supprimées car nous devons les garder pour l'historique des plannings et les statistiques. Mais les données personnelles sont anonymisées.
        $update = array('commentaires' => null, 'refus' => null, 'heures' => null, 'solde_prec' => null, 'solde_actuel' => null, 'recup_prec' => null,
            'recup_actuel' => null, 'reliquat_prec' => null, 'reliquat_actuel' => null, 'anticipation_prec' => null, 'anticipation_actuel' => null);

        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update('conges', $update, "`perso_id` IN ($liste)");

        // Suppresion des informations sur les récupérations
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->delete("recuperations", "`perso_id` IN ($liste)");

        // Suppression des informations sur les heures de présence
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->delete("planning_hebdo", "`perso_id` IN ($liste)");
    }

    public function fetch($tri="nom", $actif=null, $name=null)
    {
        $filter=array();

        // Filtre selon le champ actif (administratif, service public)
        $actif=htmlentities($actif, ENT_QUOTES|ENT_IGNORE, "UTF-8", false);
        if ($actif) {
            $filter['actif'] = $actif;
        }

        // Filtre selon le champ supprime
        $supprime=join(',', $this->supprime);
        $filter['supprime'] = "IN{$supprime}";

        if (!$GLOBALS['config']['Absences-notifications-agent-par-agent']) {
            $this->responsablesParAgent = false;
        }

        if ($this->responsablesParAgent) {
            $db=new db();
            $db->selectLeftJoin(
                array('personnel', 'id'),
                array('responsables', 'perso_id'),
                array('id', 'nom', 'prenom', 'mail', 'mails_responsables', 'statut', 'categorie', 'service', 'actif', 'droits', 'sites', 'check_ics', 'check_hamac'),
                array('responsable', 'notification'),
                $filter,
                array(),
                "ORDER BY $tri"
      );
        } else {
            $db=new db();
            $db->select2("personnel", null, $filter, "ORDER BY $tri");
        }

        $all=$db->result;

        // Si pas de résultat, on quitte
        if (!$db->result) {
            return false;
        }

        //	By default $result=$all
        $result=array();
        foreach ($all as $elem) {
            if (empty($result[$elem['id']])) {
                $result[$elem['id']]=$elem;
                $result[$elem['id']]['sites']=json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                $result[$elem['id']]['mails_responsables'] = explode(";", html_entity_decode($elem['mails_responsables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));

                // Contrôle des calendriers ICS distants : Oui/Non ?
                $check_ics = json_decode($result[$elem['id']]['check_ics']);
                $result[$elem['id']]['ics_1'] = !empty($check_ics[0]);
                $result[$elem['id']]['ics_2'] = !empty($check_ics[1]);
                $result[$elem['id']]['ics_3'] = !empty($check_ics[2]);
        
                if ($this->responsablesParAgent) {
                    // Ajout des responsables et notifications
                    $result[$elem['id']]['responsables'] = array( array('responsable' => $elem['responsable'], 'notification' =>$elem['notification']));
          
                    unset($result[$elem['id']]['responsable']);
                    unset($result[$elem['id']]['notification']);
                }
            } elseif ($this->responsablesParAgent) {
                // Ajout des responsables et notifications
                $result[$elem['id']]['responsables'][] = array('responsable' => $elem['responsable'], 'notification' => $elem['notification']);
            }
        }

        //	If name, keep only matching results
        if ($name) {
            $result=array();
            foreach ($all as $elem) {
                if (pl_stristr($elem['nom'], $name) or pl_stristr($elem['prenom'], $name)) {
                    $result[$elem['id']]=$elem;
                    $result[$elem['id']]['sites']=json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    $result[$elem['id']]['mails_responsables'] = explode(";", html_entity_decode($elem['mails_responsables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));

                    // Contrôle des calendriers ICS distants : Oui/Non ?
                    $check_ics = json_decode($result[$elem['id']]['check_ics']);
                    $result[$elem['id']]['ics_1'] = !empty($check_ics[0]);
                    $result[$elem['id']]['ics_2'] = !empty($check_ics[1]);
                    $result[$elem['id']]['ics_3'] = !empty($check_ics[2]);
                }
            }
        }
  
        //	Suppression de l'utilisateur "Tout le monde"
        if (!$GLOBALS['config']['toutlemonde']) {
            unset($result[2]);
        }

        $this->elements=$result;
    }


    /**
     * @function fetchById
     * @param mixed int, array $id : id de l'agent ou tableau d'ID
     * @result array : si $id est un chiffre : $this->elements[0] contient les informations de l'agent
     * @result array : si $id est un tableau : $this->elements contient les informations des agents avec l'id des agents comme clé
     */
    public function fetchById($id)
    {
        if (is_numeric($id)) {
            $db=new db();
            $db->select("personnel", null, "id='$id'");
            $this->elements=$db->result;
            $sites = json_decode(html_entity_decode($db->result[0]['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $this->elements[0]['sites'] = $sites ? $sites : array();
            $this->elements[0]['mails_responsables']=explode(";", html_entity_decode($db->result[0]['mails_responsables'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
        } elseif (is_array($id)) {
            $ids=join(",", $id);
            $db=new db();
            $db->select2("personnel", null, array("id"=>"IN $ids"));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $this->elements[$elem['id']]=$elem;
                    $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                    $this->elements[$elem['id']]['sites'] = $sites ? $sites : array();
                    $this->elements[$elem['id']]['mails_responsables']=explode(";", html_entity_decode($elem['mails_responsables'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
                }
            }
        }
    }


    /** fetchEDTSamedi
     * @desc : recherche le numéro de tableau d'emploi du temps associé à chaque semaine pour l'agent $perso_id entre les date $debut et $fin
     * @param int $perso_id : ID de l'agent
     * @param string $debut : date de début au format YYYY-MM-DD
     * @param string $fin : date de fin au format YYYY-MM-DD
     * @return array $this->elements : tableau contenant les semaines et les tableaux associés
     * @return int $this->offset : calcul l'offset à appliquer pour la recherche, dans l'emploi du temps adéquat, des heures d'un jour donné
     * (Attention, l'offset est valide pour la dernière valeur retournée. Ce qui est suffisant pour l'utilisation de cette fonction dans l'agenda et le menudiv car dans les 2 cas, une seule semaine est recherchée et donc une seule valeur est retournée)
     */
    public function fetchEDTSamedi($perso_id, $debut, $fin)
    {
        if (!$GLOBALS['config']['EDTSamedi'] or $GLOBALS['config']['PlanningHebdo']) {
            return false;
        }

        $db=new db();
        $db->select("edt_samedi", "*", "semaine>='$debut' AND semaine<='$fin' AND perso_id='$perso_id'");
        if ($db->result) {
            foreach ($db->result as $elem) {

        // Si config['EDTSamedi'] == 1 (Emploi du temps différent les semaines avec samedi travaillé), le champ tableau n'est pas nécessairement rempli car n'existait pas au départ.
                // Dans ce cas, on le force à 2 par sécurité (si EDT Samedi est cochée, on passe au tableau 2
                if (! $elem['tableau']) {
                    $elem['tableau'] = 2;
                }

                $this->elements[$elem['semaine']] = array('semaine' => $elem['semaine'], 'tableau' => $elem['tableau']);
                $this->offset = (intval($elem['tableau']) - 1) * 7 ;
            }
        }
    }
  
    /**
     * getICSCode
     * Retourne le code ICS de l'agent. Créé le code s'il n'existe pas
     * Le code ICS est requis pour accéder au calendriers si ceux-ci sont protégés
     * @param int $id : id de l'agent
     * @return string $code : retourne le code ICS de l'agent
     */
    public function getICSCode($id)
    {
        $this->fetchById($id);
        $code = $this->elements[0]['code_ics'];
        if (!$code) {
            $code = md5(time().rand(100, 999));
            $db = new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->update('personnel', array('code_ics'=>$code), array('id'=>$id));
        }
        return $code;
    }
  
    /**
     * getICSURL
     * Retourne l'URL ICS de l'agent.
     * @param int $id : id de l'agent
     * @return string $url
     */
    public function getICSURL($id)
    {
        $url = createURL();
        $url .= "/ics/calendar.php?id=$id";
        if ($GLOBALS['config']['ICS-Code']) {
            $code = $this->getICSCode($id);
            $url .= "&amp;code=$code";
        }
        return $url;
    }

    public function update_time()
    {
        $db=new db();
        $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['config']['dbprefix']}personnel';");
        $result = isset($db->result[0]['Update_time']) ? $db->result[0]['Update_time'] : null;
        return $result;
    }
  
    public function updateEDTSamedi($eDTSamedi, $debut, $fin, $perso_id)
    {
        if (!$GLOBALS['config']['EDTSamedi'] or $GLOBALS['config']['PlanningHebdo']) {
            return false;
        }

        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->delete("edt_samedi", array('semaine' => ">=$debut", 'semaine' => "<=$fin", 'perso_id' => $perso_id));

        if (!empty($eDTSamedi)) {
            $insert=array();
            foreach ($eDTSamedi as $elem) {

        // Si config['EDTSamedi'] == 1 (Emploi du temps différent les semaines avec samedi travaillé)
                if (! is_array($elem)) {
                    $insert[] = array('perso_id' => $perso_id, 'semaine' => $elem, 'tableau' => 2 );

                // Si config['EDTSamedi'] == 2 (Emploi du temps différent les semaines avec samedi travaillé et en ouverture restreinte)
                } else {
                    $insert[] = array('perso_id' => $perso_id, 'semaine' => $elem[0], 'tableau' => $elem[1] );
                }
            }

            $db=new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->insert("edt_samedi", $insert);
        }
    }
 
    public function updateResponsibles($agents, $responsables, $notifications)
    {
        // Suppression des éléments existant dans la base de données
        $liste_agents = implode(',', $agents);
        $db = new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->delete('responsables', array('perso_id' => "IN{$liste_agents}"));

        // Insertion des nouvelles données
        $db = new dbh();
        $db->CSRFToken = $this->CSRFToken;
        $db->prepare("INSERT INTO `{$GLOBALS['config']['dbprefix']}responsables` (`perso_id`, `responsable`, `notification`) VALUES (:perso_id, :responsable, :notification);");
  
        foreach ($agents as $agent) {
            foreach ($responsables as $responsable) {
                $notification = in_array($responsable, $notifications) ? 1 : 0 ;
      
                $db->execute(array(':perso_id' => $agent, ':responsable' => $responsable, ':notification' => $notification));
            }
        }
    }
}
