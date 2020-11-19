<?php
/**
Planning Biblio, Plugin Congés Version 2.8.04
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/class.conges.php
Création : 24 juillet 2013
Dernière modification : 29 octobre 2018
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié

Description :
Fichier regroupant les fonctions utiles à la gestion des congés
Inclus dans les autres fichiers PHP du dossier conges
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
$version = $GLOBALS['version'] ?? null;

if (!$version and php_sapi_name() != 'cli') {
    include_once __DIR__."/../include/accessDenied.php";
}

require_once __DIR__."/../planningHebdo/class.planningHebdo.php";
require_once __DIR__."/../joursFeries/class.joursFeries.php";
require_once __DIR__."/../personnel/class.personnel.php";
require_once __DIR__."/../absences/class.absences.php";

use App\PlanningBiblio\WorkingHours;

class conges
{
    public $agent=null;
    public $agents_supprimes=array(0);
    public $admin=false;
    public $annee=null;
    public $bornesExclues=null;
    public $CSRFToken=null;
    public $data=array();
    public $debit=null;
    public $debut=null;
    public $elements=array();
    public $error=false;
    public $fin=null;
    public $heures=null;
    public $heures2=null;
    public $id=null;
    public $information = true;
    public $message=null;
    public $minutes=null;
    public $perso_id=null;
    public $recupId=null;
    public $samedis=array();
    public $sites=array();
    public $supprime = true;
    public $valide=null;

    public function __construct()
    {
    }

    public function add($data)
    {
        $data['fin']=$data['fin']?$data['fin']:$data['debut'];
        $data['debit']=isset($data['debit'])?$data['debit']:"credit";
        $data['hre_debut']=$data['hre_debut']?$data['hre_debut']:"00:00:00";
        $data['hre_fin']=$data['hre_fin']?$data['hre_fin']:"23:59:59";
        $data['heures']=$data['heures'].".".$data['minutes'];
        $data['debut']=dateSQL($data['debut']);
        $data['fin']=dateSQL($data['fin']);

        $data = $this->applyHalfDays($data);

        // Enregistrement du congé
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->insert("conges", array(
            'debut'         => $data['debut'] . ' ' . $data['hre_debut'],
            'fin'           => $data['fin'] . ' ' . $data['hre_fin'],
            'halfday'       => $data['halfday'] ? 1 : 0,
            'start_halfday' => $data['start_halfday'] ?? '',
            'end_halfday'   => $data['end_halfday'] ?? '',
            'commentaires'  => $data['commentaires'],
            'heures'        => $data['heures'],
            'debit'         => $data['debit'],
            'perso_id'      => $data['perso_id'],
            'saisie_par'    => $_SESSION['login_id']
        ));

        // Récupération de l'id du congé enregistré
        $this->id=0;
        $db=new db();
        $db->select("conges", "MAX(id) AS id", "debut='{$data['debut']} {$data['hre_debut']}' AND fin='{$data['fin']} {$data['hre_fin']}' 
      AND heures='{$data['heures']}' AND perso_id='{$data['perso_id']}'");
        if ($db->result) {
            $this->id=$db->result[0]['id'];
        }
    }

    public function calculCredit($debut, $hre_debut, $fin, $hre_fin, $perso_id)
    {
        // Calcul du nombre d'heures correspondant aux congés demandés
        $current=$debut;
        $difference=0;
        // Pour chaque date
        while ($current<=$fin) {

      // On ignore les jours de fermeture
            $j=new joursFeries();
            $j->fetchByDate($current);
            if (!empty($j->elements)) {
                foreach ($j->elements as $elem) {
                    if ($elem['fermeture']) {
                        $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
                        continue 2;
                    }
                }
            }

            // On consulte le planning de présence de l'agent
            $p=new planningHebdo();
            $p->perso_id=$perso_id;
            $p->debut=$current;
            $p->fin=$current;
            $p->valide=true;
            $p->fetch();
            // Si le planning n'est pas validé pour l'une des dates, on affiche un message d'erreur et on arrête le calcul
            if (empty($p->elements)) {
                $this->error=true;
                $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
                break;
            }
            // Sinon, on calcule les heures d'absence
            $d=new datePl($current);
            $semaine=$d->semaine3;
            $jour=$d->position?$d->position:7;
            $jour=$jour+(($semaine-1)*7)-1;

            $debutConges=$current==$debut?$hre_debut:"00:00:00";
            $finConges=$current==$fin?$hre_fin:"23:59:59";
            $debutConges=strtotime($debutConges);
            $finConges=strtotime($finConges);
      
            $wh = new WorkingHours($p->elements[0]['temps']);
            $temps = $wh->hoursOf($jour);
      
            foreach ($temps as $t) {
                $t0 = strtotime($t[0]);
                $t1 = strtotime($t[1]);
        
                $debutConges1 = $debutConges > $t0 ? $debutConges : $t0;
                $finConges1 = $finConges < $t1 ? $finConges : $t1;
                if ($finConges1 > $debutConges1) {
                    $difference += $finConges1 - $debutConges1;
                }
            }
        
            $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }
        $this->minutes=$difference/60;                                      // nombre de minutes (ex 2h30 => 150)
    $this->heures=number_format($difference/3600, 2, '.', '');         // heures et centièmes (ex 2h30 => 2.50)
    $this->heures2 = heure4($this->heures);                             // heures et minutes (ex: 2h30 => 2h30)
    }

    /**
    * @method calculCreditRecup
    * @param int $perso_id
    * @param string $date, format YYYY-MM-DD HH:ii:ss
    * Calcule les crédits de récupération disponible pour l'agent $perso_id à la date $date
    * Les crédits obtenus à des dates supérieures sont déduits
    */
    public function calculCreditRecup($perso_id, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $db = new db();
        $db->select2('personnel', 'comp_time', array('id' => $perso_id));
    
        // $balance1 : solde à la date choisie
        $balance1 = (float) $db->result[0]['comp_time'];

        // Date à laquelle le compteur doit être remis à zéro
        $reset_date = strtotime("09/01");
        if ($reset_date <= time()) {
            $reset_date = strtotime('+1 year', $reset_date);
        }
        $reset_date = date('Y-m-d', $reset_date);

        // Mise à zéro du compte si la date choisie est après la remise à zéro
        if ($date >= $reset_date) {
            $balance1 = 0;
        }
    
        // $balance2 : solde définitif, disponible à la date du dernier ajout
        $balance2 = $balance1;
        // $balance3 : solde prévisionnel à la date choisie
        $balance3 = $balance1;

        $last = null;

        $db = new db();
        $db->select2('recuperations', null, array('perso_id' => $perso_id), "ORDER BY `date`");
        $recup_tab = $db->result;

        if (empty($recup_tab)) {
            $recup_tab = array();
        }

        foreach ($recup_tab as $elem) {

            // On adapte les compteurs avec les enregistrements de la table récupération
            // - si la date choisie est inférieure à la date de remise à zéro
            // - ou si la date de l'enregistrement est supérieure ou égale à la date de remise à zéro
            if ($date < $reset_date or $elem['date'] >= $reset_date) {

                // On ajoute les demandes de crédits non validées au solde prévisionnel
                if ($elem['valide'] == 0 and ($elem['valide_n1'] >= 0 or $GLOBALS['config']['Conges-Validation-N2'] == 0)) {
                    $balance3 += (float) $elem['heures'];
                }

                // On ajoute les crédits validés aux compteurs si la date choisie est supérieure à la date de remise à zéro
                if ($elem['valide'] > 0 and $date >= $reset_date) {
                    $balance1 += (float) $elem['heures'];
                    $balance3 += (float) $elem['heures'];
                }

                // Retire les crédits applicables aux dates supérieures à celle choisie
                if ($elem['date'] > $date) {

                    // Crédits validés ou non
                    $balance3 -= (float) $elem['heures'];

                    // Crédits validés
                    if ($elem['valide'] > 0) {
                        $balance1 -= (float) $elem['heures'];
                    }
                }
            }

            $last = end($recup_tab);
            $last = $last['date'];
        }

        $db = new db();
        $db->select2('conges', null, array('perso_id' => $perso_id, 'debit' => 'recuperation'));
        $leave_tab = $db->result;

        // On déduit les demandes de récupérations non-validées au solde prévisionnel
        if (!empty($leave_tab)) {
            foreach ($leave_tab as $elem) {

                // On adapte le compteur prévisionnel avec les enregistrements de la table congés
                // - si la date choisie est inférieure à la date de remise à zéro
                // - ou si la date de l'enregistrement est supérieure ou égale à la date de remise à zéro
                if ($date < $reset_date or $elem['debut'] >= $reset_date) {
                    if ($elem['valide'] == 0 and ($elem['valide_n1'] >= 0 or $GLOBALS['config']['Conges-Validation-N2'] == 0)) {
                        $balance3 -= (float) $elem['heures'];
                    }
                }
            }
        }


        return array($date, $balance1, $last, $balance2, $balance3);
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

        $filter = array(
            'perso_id'      => $perso_id,
            'debut'         => "<$fin",
            'fin'           => ">$debut",
            'supprime'      => 0,
            'information'   => 0
        );

        if ($valide==true) {
            $filter["valide"]=">0";
        }
    
        $db=new db();
        $db->select2("conges", null, $filter);
        if ($db->result) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        // Marque une demande de congé comme supprimée
        // Contrôle si le congé avait été validé.
        // Dans ce cas :
        // - Recredite les comptes débités
        // - Ajoute une ligne faisant apparaître les crédits dans le tableau Congés

        $id=$this->id;

        // Récupération des infos à partir de la table congés
        $db=new db();
        $db->select("conges", null, "id='$id'");
        if ($db->result) {
            $result=$db->result[0];
            $heures=$result['heures'];
            $perso_id=$result['perso_id'];
            $valide=$result['valide'];
            $credit=floatval($result['solde_prec'])-floatval($result['solde_actuel']);
            $recup=floatval($result['recup_prec'])-floatval($result['recup_actuel']);
            $reliquat=floatval($result['reliquat_prec'])-floatval($result['reliquat_actuel']);
            $anticipation=floatval($result['anticipation_actuel'])-floatval($result['anticipation_prec']);

            // Si le congés a été validé, mise à jour des crédits dans la table personnel
            if ($valide>0) {
                $db=new db();
                $db->select("personnel", null, "id=$perso_id");
                $perso_credit=$db->result[0]['conges_credit'];
                $perso_reliquat=$db->result[0]['conges_reliquat'];
                $perso_anticipation=$db->result[0]['conges_anticipation'];
                $perso_recup=$db->result[0]['comp_time'];

                $perso_credit_new=floatval($perso_credit)+floatval($credit);
                $perso_reliquat_new=floatval($perso_reliquat)+floatval($reliquat);
                $perso_recup_new=floatval($perso_recup)+floatval($recup);
                $perso_anticipation_new=floatval($perso_anticipation)-floatval($anticipation);

                $update=array("conges_credit"=>$perso_credit_new, "conges_reliquat"=>$perso_reliquat_new,
      "conges_anticipation"=>$perso_anticipation_new, "comp_time"=>$perso_recup_new);
                $db=new db();
                $db->CSRFToken = $this->CSRFToken;
                $db->update("personnel", $update, array("id"=>$perso_id));

                // Ajout d'une ligne d'information sur les crédits
                $insert=array();
                $keys=array_keys($result);
                foreach ($keys as $key) {
                    if ($key!="id" and !is_numeric($key)) {
                        $insert[$key]=$result[$key];
                    }
                }
                if (!empty($insert)) {
                    $insert["solde_prec"]=$perso_credit;
                    $insert["recup_prec"]=$perso_recup;
                    $insert["reliquat_prec"]=$perso_reliquat;
                    $insert["anticipation_prec"]=$perso_anticipation;
                    $insert["solde_actuel"]=$perso_credit_new;
                    $insert["recup_actuel"]=$perso_recup_new;
                    $insert["reliquat_actuel"]=$perso_reliquat_new;
                    $insert["anticipation_actuel"]=$perso_anticipation_new;
                    $insert["information"]=$_SESSION['login_id'];
                    $insert["info_date"]=date("Y-m-d H:i:s");
                    $db=new db();
                    $db->CSRFToken = $this->CSRFToken;
                    $db->insert("conges", $insert);
                }
            }
        }

        // Marque la demande de congé comme supprimée dans la table conges
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("conges", array("supprime"=>$_SESSION['login_id'],"suppr_date"=>date("Y-m-d H:i:s")), array("id"=>$id));
    }


    public function fetch()
    {
        // Filtre de recherche
        $filter="1";

        // Perso_id
        if ($this->perso_id) {
            $filter.=" AND `perso_id`='{$this->perso_id}'";
        }

        // Date, debut, fin
        $debut=$this->debut;
        $fin=$this->fin;
        $date=date("Y-m-d")." 23:59:59";
        if ($debut) {
            $fin=$fin?$fin:$date;
            if ($this->bornesExclues) {
                $filter.=" AND `debut`<'$fin' AND `fin`>'$debut'";
            } else {
                $filter.=" AND `debut`<='$fin' AND `fin`>='$debut'";
            }
        } else {
            if ($this->bornesExclues) {
                $filter.=" AND `fin`>'$date'";
            } else {
                $filter.=" AND `fin`>='$date'";
            }
        }

        // Suppressions et informations
        if ($this->information == false) {
            $filter .= " AND `information` = '0' ";
        }
    
        if ($this->supprime == false) {
            $filter .= " AND `supprime` = '0' ";
        }
    
        if ($this->debit) {
            $filter .= " AND ( `debit` = '{$this->debit}' OR `debit` IS NULL ) ";
        }

        // Recherche des agents actifs seulement
        $perso_ids=array(0);
        $p=new personnel();
        // Si précisé, recherche également les agents supprimés
    $p->supprime=$this->agents_supprimes; 	// array(0,1,2), default : array(0);
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

        // Filtre pour agents actifs seulement et recherche avec nom de l'agent
        $perso_ids=join(",", $perso_ids);
        $filter.=" AND `perso_id` IN ($perso_ids)";

        // Valide
        if ($this->valide) {
            $filter.=" AND `valide`>0 AND `supprime`=0 AND `information`=0";
        }
  
        // Filtre avec ID, si ID, les autres filtres sont effacés
        if ($this->id) {
            $filter="`id`='{$this->id}'";
        }

        // Récupération des noms des agents
        $p=new personnel();
        $p->fetch("nom", "Actif");
        $agents=$p->elements;

        $db=new db();
        $db->select("conges", "*", $filter, "ORDER BY debut,fin,saisie");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $elem['nom']=array_key_exists($elem['perso_id'], $agents)?$agents[$elem['perso_id']]['nom']:null;
                $elem['prenom']=array_key_exists($elem['perso_id'], $agents)?$agents[$elem['perso_id']]['prenom']:null;
                $elem['debutAff']=dateFr($elem['debut'], true);
                $elem['finAff']=dateFr($elem['fin'], true);
                $this->elements[]=$elem;
            }
        }
    }


    public function fetchAllCredits()
    {
        // Recheche de tous les crédits de congés afin de les afficher dans la page congés / Crédits

        // Affichage ou non des crédits des agents supprimés
        $supprime=join("','", $this->agents_supprimes);

        // Recherche des agents
        // N'affiche que les agents des sites gérés (Multisites seulement)
        $sitesReq=null;
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $tmp=array();
            if (!empty($this->sites)) {
                foreach ($this->sites as $elem) {
                    $tmp[]="sites LIKE '%\"$elem\"%'";
                    $sitesReq=" AND (".join(" OR ", $tmp).") ";
                }
            }
        }

        $db=new db();
        $db->select("personnel", "id,nom,prenom,conges_credit,conges_reliquat,conges_anticipation,comp_time,conges_annuel", "`supprime` IN ('$supprime') AND `id`<>'2' AND actif like 'Actif' $sitesReq");
        if (!$db->result) {
            return false;
        }

        // Création du tableau avec les noms des agents et les crédits annuels
        $tab=array();
        foreach ($db->result as $elem) {
            $tab[$elem['id']]=$elem;
            $tab[$elem['id']]["agent"]=$elem["nom"]." ".substr($elem["prenom"], 0, 1);
            $tab[$elem['id']]['conge_annuel']=$elem['conges_annuel'];
        }

        // Crédits initiaux
        /* Utilise le champ info_date pour rechercher la première mise à jour des crédits de l'année.
        Cette mise à jour peut être faite :
          - par le cron au 1er septembre
          - par un administrateur lors de la création de l'agent en cours d'année
          - par un administrateur lors de la 1ere modification de crédits suivant la création de l'agent si les crédits étaient initialement à 0
        */

        $debut=date("n")<9?date("Y")-1:date("Y");
        $debut.="-09-01 00:00:00";
        $db=new db();
        $db->select("conges", null, "`info_date` >= '$debut'", "ORDER BY `info_date`");

        if ($db->result) {
            foreach ($db->result as $elem) {
                if (!array_key_exists($elem['perso_id'], $tab)) {
                    continue;
                }
                if (!array_key_exists("conge_initial", $tab[$elem['perso_id']])) {
                    $tab[$elem['perso_id']]['conge_initial']=$elem['solde_actuel'];
                    $tab[$elem['perso_id']]['reliquat_initial']=$elem['reliquat_actuel'];
                    $tab[$elem['perso_id']]['recup_initial']=$elem['recup_actuel'];
                    $tab[$elem['perso_id']]['anticipation_initial']=$elem['anticipation_actuel'];
                }
            }
        }

        // Crédits actuels
        // Sélection des derniers congés validés
        $db=new db();
        $db->select("conges", null, "valide>0", "ORDER BY `validation` desc");

        if ($db->result) {
            foreach ($db->result as $elem) {
                if (!array_key_exists($elem['perso_id'], $tab)) {
                    continue;
                }
                if (!array_key_exists("maj1", $tab[$elem['perso_id']])) {
                    $tab[$elem['perso_id']]['conge_restant']=$elem['solde_actuel'];
                    $tab[$elem['perso_id']]['reliquat_restant']=$elem['reliquat_actuel'];
                    $tab[$elem['perso_id']]['recup_restant']=$elem['recup_actuel'];
                    $tab[$elem['perso_id']]['anticipation_restant']=$elem['anticipation_actuel'];
                    $tab[$elem['perso_id']]['validation']=$elem['validation'];
                    $tab[$elem['perso_id']]['maj1']=true;
                }
            }
        }

        // Crédits actuels
        // Sélection des dernières mises à jour de crédits
        $db=new db();
        $db->select("conges", null, "information>0", "ORDER BY `info_date` desc");

        if ($db->result) {
            foreach ($db->result as $elem) {
                if (!array_key_exists($elem['perso_id'], $tab)) {
                    continue;
                }
                if (!array_key_exists("validation", $tab[$elem['perso_id']])) {
                    $tab[$elem['perso_id']]['validation']="0000-00-00 00:00:00";
                }
                if (!array_key_exists("maj2", $tab[$elem['perso_id']]) and $elem['info_date']>$tab[$elem['perso_id']]['validation']) {
                    $tab[$elem['perso_id']]['conge_restant']=$elem['solde_actuel'];
                    $tab[$elem['perso_id']]['reliquat_restant']=$elem['reliquat_actuel'];
                    $tab[$elem['perso_id']]['recup_restant']=$elem['recup_actuel'];
                    $tab[$elem['perso_id']]['anticipation_restant']=$elem['anticipation_actuel'];
                    $tab[$elem['perso_id']]['validation']=$elem['info_date'];
                    $tab[$elem['perso_id']]['maj2']=true;
                }
            }
        }


        // Crédits actuels
        // Sélection des dernières mises à jour de récup
        $db=new db();
        $db->select("recuperations", null, "valide>0", "ORDER BY `validation` desc");

        if ($db->result) {
            foreach ($db->result as $elem) {
                if (!array_key_exists($elem['perso_id'], $tab)) {
                    continue;
                }
                if (empty($tab[$elem['perso_id']]['validation'])) {
                    $tab[$elem['perso_id']]['validation'] = '0000-00-00 00:00:00';
                }
                if (!array_key_exists("maj3", $tab[$elem['perso_id']]) and $elem['validation']>$tab[$elem['perso_id']]['validation']) {
                    $tab[$elem['perso_id']]['recup_restant']=$elem['solde_actuel'];
                    $tab[$elem['perso_id']]['maj3']=true;
                }
            }
        }


        // Calcul des crédits en attente de validation
        // Les crédits en attente sont égaux aux crédits validés, on y ajoutera ensuite les demandes non validées
        $perso_ids=array_keys($tab);
        foreach ($perso_ids as $perso_id) {
            $tab[$perso_id]['conge_en_attente'] = isset($tab[$perso_id]['conge_restant']) ? $tab[$perso_id]['conge_restant'] : 0 ;
            $tab[$perso_id]['reliquat_en_attente'] = isset($tab[$perso_id]['reliquat_restant']) ? $tab[$perso_id]['reliquat_restant'] : 0 ;
            $tab[$perso_id]['recup_en_attente'] = isset($tab[$perso_id]['recup_restant']) ? $tab[$perso_id]['recup_restant'] : 0 ;
            $tab[$perso_id]['anticipation_en_attente'] = isset($tab[$perso_id]['anticipation_restant']) ? $tab[$perso_id]['anticipation_restant'] : 0 ;
        }


        // Sélection des demandes non validées
        $db=new db();
        $db->select("conges", null, "`valide`='0' AND `supprime`='0' AND `information`='0' AND `saisie`>='$debut' AND `heures`>0", "ORDER BY `saisie`");

        if ($db->result) {
            foreach ($db->result as $elem) {
                if (!array_key_exists($elem['perso_id'], $tab)) {
                    continue;
                }
                $heures=floatval($elem['heures']);
                // Déduisons en priorité les reliquats
                if ($tab[$elem['perso_id']]['reliquat_en_attente']>0 and floatval($tab[$elem['perso_id']]['reliquat_en_attente'])-$heures<0) {
                    $heures-=floatval($tab[$elem['perso_id']]['reliquat_en_attente']);
                    $tab[$elem['perso_id']]['reliquat_en_attente']=0;
                } elseif ($tab[$elem['perso_id']]['reliquat_en_attente']>0) {
                    $tab[$elem['perso_id']]['reliquat_en_attente']-=$heures;
                    continue;
                }

                // Reliquats utilisés
                // Puis les récupérations
                if ($elem['debit']=="recuperation") {
                    if ($tab[$elem['perso_id']]['recup_en_attente']-$heures<0) {
                        $heures-=$tab[$elem['perso_id']]['recup_en_attente'];
                        $tab[$elem['perso_id']]['recup_en_attente']=0;
                        // Et le crédit si récup insuffisantes
                        if ($tab[$elem['perso_id']]['conge_en_attente']-$heures<0) {
                            $heures-=$tab[$elem['perso_id']]['conge_en_attente'];
                            $tab[$elem['perso_id']]['conge_en_attente']=0;
                        } else {
                            $tab[$elem['perso_id']]['conge_en_attente']-=$heures;
                            continue;
                        }
                    } else {
                        $tab[$elem['perso_id']]['recup_en_attente']-=$heures;
                        continue;
                    }
                    // Puis les crédits
                } else {
                    if ($tab[$elem['perso_id']]['conge_en_attente']-$heures<0) {
                        $heures-=$tab[$elem['perso_id']]['conge_en_attente'];
                        $tab[$elem['perso_id']]['conge_en_attente']=0;
                        // Et récup si crédit insuffisant
                        if ($tab[$elem['perso_id']]['recup_en_attente']-$heures<0) {
                            $heures-=$tab[$elem['perso_id']]['recup_en_attente'];
                            $tab[$elem['perso_id']]['recup_en_attente']=0;
                        } else {
                            $tab[$elem['perso_id']]['recup_en_attente']-=$heures;
                            continue;
                        }
                    } else {
                        $tab[$elem['perso_id']]['conge_en_attente']-=$heures;
                        continue;
                    }
                }
                // Et enfin le solde débiteur
                $tab[$elem['perso_id']]['anticipation_en_attente']+=$heures;
            }
        }


        // Calcul des crédits utilisés et demandés en attente de validation
        $perso_ids=array_keys($tab);
        foreach ($perso_ids as $perso_id) {

      // Initilisation
            $tab[$perso_id]['conge_initial'] = isset($tab[$perso_id]['conge_initial']) ? $tab[$perso_id]['conge_initial'] : 0;
            $tab[$perso_id]['conge_restant'] = isset($tab[$perso_id]['conge_restant']) ? $tab[$perso_id]['conge_restant'] : 0;
            $tab[$perso_id]['reliquat_initial'] = isset($tab[$perso_id]['reliquat_initial']) ? $tab[$perso_id]['reliquat_initial'] : 0;
            $tab[$perso_id]['reliquat_restant'] = isset($tab[$perso_id]['reliquat_restant']) ? $tab[$perso_id]['reliquat_restant'] : 0;
            $tab[$perso_id]['recup_initial'] = isset($tab[$perso_id]['recup_initial']) ? $tab[$perso_id]['recup_initial'] : 0;
            $tab[$perso_id]['recup_restant'] = isset($tab[$perso_id]['recup_restant']) ? $tab[$perso_id]['recup_restant'] : 0;
            $tab[$perso_id]['anticipation_restant'] = isset($tab[$perso_id]['anticipation_restant']) ? $tab[$perso_id]['anticipation_restant'] : 0;
            $tab[$perso_id]['anticipation_initial'] = isset($tab[$perso_id]['anticipation_initial']) ? $tab[$perso_id]['anticipation_initial'] : 0;

            // Crédits utilisés
            $tab[$perso_id]['conge_utilise']=$tab[$perso_id]['conge_initial']-$tab[$perso_id]['conge_restant'];
            $tab[$perso_id]['reliquat_utilise']=$tab[$perso_id]['reliquat_initial']-$tab[$perso_id]['reliquat_restant'];
            $tab[$perso_id]['recup_utilise']=$tab[$perso_id]['recup_initial']-$tab[$perso_id]['recup_restant'];
            $tab[$perso_id]['anticipation_utilise']=$tab[$perso_id]['anticipation_restant']-$tab[$perso_id]['anticipation_initial'];

            // Crédits demandés en attente
            $tab[$perso_id]['conge_demande']=$tab[$perso_id]['conge_initial']-$tab[$perso_id]['conge_en_attente'];
            $tab[$perso_id]['reliquat_demande']=$tab[$perso_id]['reliquat_initial']-$tab[$perso_id]['reliquat_en_attente'];
            $tab[$perso_id]['recup_demande']=$tab[$perso_id]['recup_initial']-$tab[$perso_id]['recup_en_attente'];
            $tab[$perso_id]['anticipation_demande']=$tab[$perso_id]['anticipation_en_attente']-$tab[$perso_id]['anticipation_initial'];

            // Classe bold si différence entre crédits validés et demandés
            $tab[$perso_id]['conge_classe']=$tab[$perso_id]['conge_demande']!=$tab[$perso_id]['conge_utilise']?"bold":null;
            $tab[$perso_id]['reliquat_classe']=$tab[$perso_id]['reliquat_demande']!=$tab[$perso_id]['reliquat_utilise']?"bold":null;
            $tab[$perso_id]['recup_classe']=$tab[$perso_id]['recup_demande']!=$tab[$perso_id]['recup_utilise']?"bold":null;
            $tab[$perso_id]['anticipation_classe']=$tab[$perso_id]['anticipation_demande']!=$tab[$perso_id]['anticipation_utilise']?"bold":null;
        }

        $this->elements=$tab;
    }


    public function fetchCredit()
    {
        if (!$this->perso_id) {
            $this->elements=array("annuel"=>null,"anticipation"=>null,"credit"=>null,"recup"=>null,"reliquat"=>null,
    "annuelHeures"=>null, "anticipationHeures"=>null, "creditHeures"=>null, "recupHeures"=>null, "reliquatHeures"=>null,
    "annuelMinutes"=>null, "anticipationMinutes"=>null, "creditMinutes"=>null, "recupMinutes"=>null, "reliquatMinutes"=>null,
    "annuelCents"=>null, "anticipationCents"=>null, "creditCents"=>null, "recupCents"=>null, "reliquatCents"=>null );
        } else {
            $db=new db();
            $db->select("personnel", "conges_credit,conges_reliquat,conges_anticipation,comp_time,conges_annuel", "`id`='{$this->perso_id}'");
            if ($db->result) {
                $annuel = $db->result[0]['conges_annuel'] ? $db->result[0]['conges_annuel'] : 0;
                $anticipation = $db->result[0]['conges_anticipation'] ? $db->result[0]['conges_anticipation'] : 0;
                $credit = $db->result[0]['conges_credit'] ? $db->result[0]['conges_credit'] : 0;
                $recup = $db->result[0]['comp_time'] ? $db->result[0]['comp_time'] : 0;
                $reliquat = $db->result[0]['conges_reliquat'] ? $db->result[0]['conges_reliquat'] : 0;

                $annuelHeures=floor($annuel);
                $anticipationHeures=floor($anticipation);
                $creditHeures=floor($credit);
                $recupHeures=floor($recup);
                $reliquatHeures=floor($reliquat);

                $annuelCents=(round(($annuel-$annuelHeures)*60)/2)*2;
                $anticipationCents=(round(($anticipation-$anticipationHeures)*60)/2)*2;
                $creditCents=(round(($credit-$creditHeures)*60)/2)*2;
                $recupCents=(round(($recup-$recupHeures)*60)/2)*2;
                $reliquatCents=(round(($reliquat-$reliquatHeures)*60)/2)*2;

                $annuelMinutes=$annuelCents*0.6;
                $anticipationMinutes=$anticipationCents*0.6;
                $creditMinutes=$creditCents*0.6;
                $recupMinutes=$recupCents*0.6;
                $reliquatMinutes=$reliquatCents*0.6;

                $this->elements=array("annuel"=>$annuel, "anticipation"=>$anticipation, "credit"=>$credit, "recup"=>$recup, "reliquat"=>$reliquat,
      "annuelHeures"=>$annuelHeures, "anticipationHeures"=>$anticipationHeures, "creditHeures"=>$creditHeures, "recupHeures"=>$recupHeures, "reliquatHeures"=>$reliquatHeures,
      "annuelMinutes"=>$annuelMinutes, "anticipationMinutes"=>$anticipationMinutes, "creditMinutes"=>$creditMinutes, "recupMinutes"=>$recupMinutes, "reliquatMinutes"=>$reliquatMinutes,
      "annuelCents"=>$annuelCents, "anticipationCents"=>$anticipationCents, "creditCents"=>$creditCents, "recupCents"=>$recupCents, "reliquatCents"=>$reliquatCents );
            }
        }
    }

    public function getCET()
    {
        $where=$this->perso_id?"perso_id='{$this->perso_id}'":"1";

        if ($this->annee) {
            $where.=" AND `annee`='{$this->annee}'";
        }

        if ($this->id) {
            $where="id='{$this->id}'";
        }

        $db=new db();
        $db->select("conges_CET", null, $where);
        if ($db->result) {
            $this->elements=$db->result;
        }
    }

    public function getRecup()
    {
        $debut=$this->debut?$this->debut:date("Y-m-d", strtotime("-1 month", time()));
        $fin=$this->fin?$this->fin:date("Y-m-d", strtotime("+1 year", time()));
        $filter="`date` BETWEEN '$debut' AND '$fin'";

        // Recherche avec l'id de l'agent
        if ($this->admin and $this->perso_id) {
            $filter.=" AND `perso_id`='{$this->perso_id}'";
        }

        if (!$this->admin) {
            $filter.=" AND perso_id='{$_SESSION['login_id']}'";
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

        // Filtre pour agents actifs seulement et recherche avec nom de l'agent
        $perso_ids=join(",", $perso_ids);
        $filter.=" AND `perso_id` IN ($perso_ids)";

        // Si recupId, le filtre est réinitialisé
        if ($this->recupId) {
            $filter="id='{$this->recupId}'";
        }

        $db=new db();
        $db->select("recuperations", "*", $filter, "order by date,saisie");
        if ($db->result) {
            $this->elements=$db->result;
        }
    }

    public function getResponsables($debut=null, $fin=null, $perso_id)
    {
        $responsables=array();
        $droitsConges=array();
        //	Si plusieurs sites, vérifions dans l'emploi du temps quels sont les sites concernés par le conges
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $db=new db();
            $db->select("personnel", "temps", "id='$perso_id'");
            $temps=json_decode(html_entity_decode($db->result[0]['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $date=$debut;
            while ($date<=$fin) {
                // Emploi du temps si plugin planningHebdo
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
                $offset=$jour-1+($semaine*7)-7;
                if (array_key_exists($offset, $temps)) {
                    if (array_key_exists(4, $temps[$offset])) {
                        $site=$temps[$offset][4];
                    } else {
                        $site=1;
                    }
                    // Ajout du numéro du droit correspondant à la gestion des congés de ce site
                    // Validation niveau 1
                    if (!in_array((400+$site), $droitsConges) and $site) {
                        $droitsConges[]=400+$site;
                    }
                    // Validation niveau 2
                    if (!in_array((600+$site), $droitsConges) and $site) {
                        $droitsConges[]=600+$site;
                    }
                }
                $date=date("Y-m-d", strtotime("+1 day", strtotime($date)));
            }
            // Si les jours de conges ne concernent aucun site, on ajoute les responsables de tous les sites par sécurité
            if (empty($droitsConges)) {
                for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
                    $droitsConges[]=400+$i;
                    $droitsConges[]=600+$i;
                }
            }
        }
        // Si un seul site, le droit de gestion de congés niveau 1 est 401, le droit de gestion de congés niveau 2 est 601
        else {
            $droitsConges=array(401,601);
        }

        $db=new db();
        $db->select("personnel", null, "supprime='0'");
        foreach ($db->result as $elem) {
            $d=json_decode(html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            foreach ($droitsConges as $elem2) {
                if (is_array($d)) {
                    if (in_array($elem2, $d) and !in_array($elem, $responsables)) {
                        $responsables[]=$elem;
                    }
                }
            }
        }
        $this->responsables=$responsables;
    }

    public function getSaturday()
    {
        // Liste des samedis des 2 derniers mois
        $perso_id=isset($this->perso_id)?$this->perso_id:$_SESSION['login_id'];
        $samedis=array();
        $current=date("Y-m-d");
        while ($current>date("Y-m-d", strtotime("-2 month", time()))) {
            $d=new datePl($current);
            if ($d->position==6) {
                $samedis[$current]=array("date"=>$current,"heures"=>0,"recup"=>null);
            }
            $current=date("Y-m-d", strtotime("-1 day", strtotime($current)));
        }

        // Pour chaque samedi
        foreach ($samedis as $samedi) {
            // Vérifions si l'agent a travaillé et récupérons les heures correspondantes
            $db=new db();
            $db->select("pl_poste", "*", "date='{$samedi['date']}' AND perso_id='$perso_id' AND absent='0'");
            $heures=0;
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $heures+=diff_heures($elem['debut'], $elem['fin'], "decimal");
                }
            }
            $samedis[$samedi['date']]['heures']=number_format($heures, 2, '.', ' ');

            // Vérifions si une demande de récupération à déjà été faite
            $db=new db();
            $db->select("recuperations", "*", "date='{$samedi['date']}' AND perso_id='$perso_id'");
            if ($db->result) {
                $samedis[$samedi['date']]['recup']=$db->result[0]['etat'];
                $samedis[$samedi['date']]['valide']=$db->result[0]['valide'];
                $samedis[$samedi['date']]['heures_validees']=$db->result[0]['heures'];
            }
        }
        $this->samedis=$samedis;
    }


    public function maj($credits, $action="modif", $cron=false)
    {
        // Ajoute une ligne faisant apparaître la mise à jour des crédits dans le tableau Congés
        if ($action=="modif") {
            $db=new db();
            $db->select("personnel", "*", "id='{$this->perso_id}'");
            $old=array("conges_credit"=>$db->result[0]['conges_credit'], "comp_time"=>$db->result[0]['comp_time'],
    "conges_reliquat"=>$db->result[0]['conges_reliquat'], "conges_anticipation"=>$db->result[0]['conges_anticipation']);
        } else {
            $old=array("conges_credit"=>0, "comp_time"=>0, "conges_reliquat"=>0, "conges_anticipation"=>0);
        }

        unset($credits["conges_annuel"]);
        if ($credits!=$old) {
            $insert=array();
            $insert["perso_id"]=$this->perso_id;
            $insert["debut"]=date("Y-m-d 00:00:00");
            $insert["fin"]=date("Y-m-d 00:00:00");
            $insert["solde_prec"]=$old['conges_credit'];
            $insert["recup_prec"]=$old['comp_time'];
            $insert["reliquat_prec"]=$old['conges_reliquat'];
            $insert["anticipation_prec"]=$old['conges_anticipation'];
            $insert["solde_actuel"]=$credits['conges_credit'];
            $insert["recup_actuel"]=$credits['comp_time'];
            $insert["reliquat_actuel"]=$credits['conges_reliquat'];
            $insert["anticipation_actuel"]=$credits['conges_anticipation'];
            $insert["information"]=$cron?999999999:$_SESSION['login_id'];
            $insert["info_date"]=date("Y-m-d H:i:s");

            $db=new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->insert("conges", $insert);
        }
    }

    /** roles
     * @param int $perso_id : ID de l'agent concerné par le congés ou la récupération
     * @param boolean $accessDenied default false : afficher "accès refusé" si la page demandée ne concerne pas l'agent logué et s'il n'est pas admin
     * @return Array($adminN1, $adminN2) : tableau ayant pour 1er valeur true si l'agent logué est adminN1, false sinon, pour 2ème valeur true s'il est adminN2, false sinon
     * Affiche "accès refusé" si la page demandée ne concerne pas l'agent logué et s'il n'est pas admin
     */
    public function roles($perso_id, $accesDenied = false)
    {

    // Droits d'administration niveau 1 et niveau 2
        // Droits nécessaires en mono-site
        $droitsN1 = array(401);
        $droitsN2 = array(601);

        // Droits nécessaires en multisites avec vérification des sites attribués à l'agent concerné par le congé
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $droitsN1 = array();
            $droitsN2 = array();

            $p=new personnel();
            $p->fetchById($perso_id);

            if (is_array($p->elements[0]['sites'])) {
                foreach ($p->elements[0]['sites'] as $site) {
                    $droitsN1[] = 400 + $site;
                    $droitsN2[] = 600 + $site;
                }
            }
        }

        // Ai-je le droit d'administration niveau 1 pour le congé demandé
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

        // Ai-je le droit d'administration niveau 2 pour le congé demandé
        $adminN2 = false;
        foreach ($droitsN2 as $elem) {
            if (in_array($elem, $_SESSION['droits'])) {
                $adminN2 = true;
                break;
            }
        }

        // Affiche accès refusé si le congé ne concerne pas l'agent logué et qu'il n'est pas admin
        if ($accesDenied and !$adminN1 and !$adminN2 and $perso_id != $_SESSION['login_id']) {
            echo "<h3 style='text-align:center;'>Accès refusé</h3>\n";
            echo "<p style='text-align:center;' >\n";
            echo "<a href='javascript:history.back();'>Retour</a></p>\n";
            include(__DIR__.'/../include/footer.php');
        }

        return array($adminN1, $adminN2);
    }


    public function update($data)
    {
        $data['debit']=isset($data['debit'])?$data['debit']:"credit";
        $data['hre_debut']=$data['hre_debut']?$data['hre_debut']:"00:00:00";
        $data['hre_fin']=$data['hre_fin']?$data['hre_fin']:"23:59:59";
        $data['heures']=$data['heures'].".".$data['minutes'];
        $data['commentaires']=htmlentities($data['commentaires'], ENT_QUOTES|ENT_IGNORE, "UTF-8", false);
        $data['refus']=htmlentities($data['refus'], ENT_QUOTES|ENT_IGNORE, "UTF-8", false);
        $data['debut']=dateSQL($data['debut']);
        $data['fin']=dateSQL($data['fin']);

        $data = $this->applyHalfDays($data);

        $update=array(
            'debut'         => $data['debut'] . ' ' . $data['hre_debut'],
            'fin'           => $data['fin'] . ' ' . $data['hre_fin'],
            'halfday'       => $data['halfday'] ? 1 : 0,
            'start_halfday' => $data['start_halfday'],
            'end_halfday'   => $data['end_halfday'],
            'commentaires'  => $data['commentaires'],
            'refus'         => $data['refus'],
            'heures'        => $data['heures'],
            'debit'         => $data['debit'],
            'perso_id'      => $data['perso_id'],
            'modif'         => $_SESSION['login_id'],
            'modification'  => date("Y-m-d H:i:s")
        );

        if ($data['valide']) {
            // Validation Niveau 2
            if ($data['valide']==-1 or $data['valide']==1) {
                $update["valide"]=$data['valide']*$_SESSION['login_id']; // login_id positif si accepté, négatif si refusé
                $update["validation"]=date("Y-m-d H:i:s");
            }
            // Validation Niveau 1
            elseif ($data['valide']==-2 or $data['valide']==2) {
                $update["valide_n1"]=($data['valide']/2)*$_SESSION['login_id']; // login_id positif si accepté, négatif si refusé
                $update["validation_n1"]=date("Y-m-d H:i:s");
                $update['valide']=0;
            }
        } else {
            $update['valide']=0;
        }

        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("conges", $update, array("id"=>$data['id']));
  
        // En cas de validation, on débite les crédits dans la fiche de l'agent et on barre l'agent s'il est déjà placé dans le planning
        if ($data['valide']=="1" and !$db->error) {
            // On débite les crédits dans la fiche de l'agent
            // Recherche des crédits actuels
            $p=new personnel();
            $p->fetchById($data['perso_id']);
            $credit = floatval($p->elements[0]['conges_credit']);
            $reliquat = floatval($p->elements[0]['conges_reliquat']);
            $recuperation = floatval($p->elements[0]['comp_time']);
            $anticipation = floatval($p->elements[0]['conges_anticipation']);
            $heures = floatval($data['heures']);
      
            // Mise à jour des compteurs dans la table conges
            $updateConges=array("solde_prec"=>$credit, "recup_prec"=>$recuperation, "reliquat_prec"=>$reliquat, "anticipation_prec"=>$anticipation);

            // Si les congés et les récupérations sont traitées de la même façon (config['Conges-Recuperations'] = 0 / Assembler
            if ($data['conges-recup'] == 0) {
                // Calcul du reliquat après décompte
                $reste=0;
                $reliquat=$reliquat-$heures;
                if ($reliquat<0) {
                    $reste=-$reliquat;
                    $reliquat=0;
                }
                $reste2=0;
                // Calcul du crédit de récupération
                if ($data["debit"]=="recuperation") {
                    $recuperation=$recuperation-$reste;
                    if ($recuperation<0) {
                        $reste2=-$recuperation;
                        $recuperation=0;
                    }
                }
                // Calcul du crédit de congés
                elseif ($data["debit"]=="credit") {
                    $credit=$credit-$reste;
                    if ($credit<0) {
                        $reste2=-$credit;
                        $credit=0;
                    }
                }
                // Si après tous les débits, il reste des heures, on débit le crédit restant
                $reste3=0;
                if ($reste2) {
                    if ($data["debit"]=="recuperation") {
                        $credit=$credit-$reste2;
                        if ($credit<0) {
                            $reste3=-$credit;
                            $credit=0;
                        }
                    } elseif ($data["debit"]=="credit") {
                        $recuperation=$recuperation-$reste2;
                        if ($recuperation<0) {
                            $reste3=-$recuperation;
                            $recuperation=0;
                        }
                    }
                }

                if ($reste3) {
                    $anticipation=floatval($anticipation)+$reste3;
                }
            }
      
            // Si les congés et les récupérations sont traitées différement (config['Conges-Recuperations'] = 1 / Dissocier
            else {
                if ($data["debit"]=="credit") {
                    // Calcul du reliquat après décompte
                    $reste=0;
                    $reliquat=$reliquat-$heures;
                    if ($reliquat<0) {
                        $reste=-$reliquat;
                        $reliquat=0;
                    }
                    // Calcul du crédit de congés
                    $credit=$credit-$reste;
                    if ($credit<0) {
                        $reste=-$credit;
                        $credit=0;
                    } else {
                        $reste = 0;
                    }
                    if ($reste) {
                        $anticipation=floatval($anticipation)+$reste;
                    }
                }
        
                // Calcul du crédit de récupération
                else {
                    $recuperation = $recuperation - $heures;
                }
            }

            // Mise à jour des compteurs dans la table personnel
            $updateCredits=array("conges_credit"=>$credit,"conges_reliquat"=>$reliquat,"comp_time"=>$recuperation,"conges_anticipation"=>$anticipation);
            $db=new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->update("personnel", $updateCredits, array("id"=>$data["perso_id"]));

            // Mise à jour des compteurs dans la table conges
            $updateConges=array_merge($updateConges, array("solde_actuel"=>$credit,"reliquat_actuel"=>$reliquat,"recup_actuel"=>$recuperation,"anticipation_actuel"=>$anticipation));
            $db=new db();
            $db->CSRFToken = $this->CSRFToken;
            $db->update("conges", $updateConges, array("id"=>$data['id']));
        }
    }

    public function updateCETCredits()
    {
        $data=$this->data;
        if (!empty($data) and $data['valide_n2']>0) {
            $jours=$data['jours'];
            $heures=intval($jours)*7;
            $db=new db();
            $db->query("UPDATE `{$GLOBALS['config']['dbprefix']}personnel` SET `conges_reliquat`=(`conges_reliquat`-$heures)
	WHERE `id`='{$data['perso_id']}'");

            // METTRE A JOUR LES CHAMPS solde_prec et solde_actuel
      // Les afficher dans le tableau si demande validée
        }
    }

    public function all($from, $to, $rejected = 0)
    {
        $this->debut = $from;
        $this->fin = $to;
        $this->valide = false;
        $this->information = false;
        $this->supprime = false;

        $filtered = array();
        $this->fetch();
        $all = $this->elements;

        foreach ($all as $holiday) {
            # Exclude rejected.
            if (!$rejected && $holiday['valide'] < 0) {
                continue;
            }

            $filtered[] = $holiday;
        }

        return $filtered;

    }

    public static function exists($agent_id, $from, $to, $id = null) {
        $db = new db();
        $db->select('conges', null, "`id`<>'$id' AND `perso_id`='$agent_id' AND `debut` < '$to' AND `fin` > '$from' AND `supprime`='0' AND `information`='0' AND `valide`>='0' ", "ORDER BY `debut`,`fin`");
        if (!$db->result) {
            return null;
        }

        return array(
            'from'  => $db->result[0]['debut'],
            'to'    => $db->result[0]['fin']
        );
    }

    private function applyHalfDays($data)
    {
        // Ability to request half day.
        $data['halfday'] = isset($data['halfday']) ? $data['halfday'] : 0;

        $config = $GLOBALS['config'];
        if ($config['Conges-Mode'] == 'jours'
            && $config['Conges-demi-journees']
            && $data['halfday']) {

            if (!$data['fin']) {
                $data['fin'] = $data['debut'];
                $data['end_halfday'] = $data['start_halfday'];
            }

            if ($data['debut'] == $data['fin']) {
                if ($data['start_halfday'] == 'morning') {
                    $data['hre_debut'] = '00:00:00';
                    $data['hre_fin'] = '12:00:00';
                }
                if ($data['start_halfday'] == 'afternoon') {
                    $data['hre_debut'] = '12:00:00';
                    $data['hre_fin'] = '23:59:59';
                }
            }

            if (strtotime($data['debut']) < strtotime($data['fin'])) {
                if ($data['start_halfday'] == 'afternoon') {
                    $data['hre_debut'] = '12:00:00';
                }
                if ($data['end_halfday'] == 'morning') {
                    $data['hre_fin'] = '12:00:00';
                }
            }
        }

        return $data;
    }

}
