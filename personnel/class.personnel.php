<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : personnel/class.personnel.php
Création : 16 janvier 2013
Dernière modification : 7 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe personnel : contient la fonction personnel::fetch permettant de rechercher les agents. 
personnel::fetch prend en paramètres $tri (nom de la colonne), $actif (string), $name (string, nom ou prenom de l'agent)

Page appelée par les autres fichiers du dossier personnel
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../include/accessDenied.php";
}

class personnel{
  public $elements=array();
  // supprime : permet de sélectionner les agents selon leur état de suppression
  // Tableau, valeur 0=pas supprimé, 1=1ère suppression (corbeille), 2=suppression définitive
  public $supprime=array(0);
  
  public $CSRFToken = null;

  public function __construct(){
  }

  public function delete($liste){
    $update=array("supprime"=>"2","login"=>"CONCAT(login,SYSDATE())","mail"=>null,"arrivee"=>null,"depart"=>null,"postes"=>null,"droits"=>null,
      "password"=>null,"commentaires"=>"Suppression définitive le ".date("d/m/Y"), "last_login"=>null, "temps"=>null, 
      "informations"=>null, "recup"=>null, "heures_travail"=>null, "heures_hebdo"=>null, "sites"=>null, "mails_responsables"=>null, "matricule"=>null, "code_ics"=>null, "url_ics"=>null);
    
    $db=new db();
    $db->CSRFToken = $this->CSRFToken;
    $db->update2("personnel",$update,"`id` IN ($liste)");

    $db=new db();
    $db->select("plugins");
    $plugins=array();
    if($db->result){
      foreach($db->result as $elem){
	$plugins[]=$elem['nom'];
      }
    }
 
    $version=$GLOBALS['config']['Version'];	// Pour autoriser les accès aux pages suppression_agents
    if(in_array("conges",$plugins)){
      include "plugins/conges/suppression_agents.php";
    }
    if($GLOBALS['config']['PlanningHebdo']){
      require_once "planningHebdo/class.planningHebdo.php";

      // recherche des personnes à exclure (congés)
      $p=new planningHebdo();
      $p->CSRFToken = $this->CSRFToken;
      $p->suppression_agents($liste);
    }
  }

  public function fetch($tri="nom",$actif=null,$name=null){
    $filter=array();

    // Filtre selon le champ actif (administratif, service public)
    $actif=htmlentities($actif,ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
    if($actif){
      $filter[]="`actif`='$actif'";
    }

    // Filtre selon le champ supprime
    $supprime=join("','",$this->supprime);
    $filter[]="`supprime` IN ('$supprime')";

    $filter=join(" AND ",$filter);

    $db=new db();
    $db->select("personnel",null,$filter,"ORDER BY $tri");
    $all=$db->result;
    if(!$db->result)
      return false;

    //	By default $result=$all
    $result=array();
    foreach($all as $elem){
      $result[$elem['id']]=$elem;
      $result[$elem['id']]['sites']=json_decode(html_entity_decode($elem['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
    }

    //	If name, keep only matching results
    if($name){
      $result=array();
      foreach($all as $elem){
	if(pl_stristr($elem['nom'],$name) or pl_stristr($elem['prenom'],$name)){
	  $result[$elem['id']]=$elem;
	  $result[$elem['id']]['sites']=json_decode(html_entity_decode($elem['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	}
      }
    }
  
    //	Suppression de l'utilisateur "Tout le monde"
    if(!$GLOBALS['config']['toutlemonde']){
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
  public function fetchById($id){
    if(is_numeric($id)){
      $db=new db();
      $db->select("personnel",null,"id='$id'");
      $this->elements=$db->result;
      $sites = json_decode(html_entity_decode($db->result[0]['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
      $this->elements[0]['sites'] = $sites ? $sites : array();
      $this->elements[0]['mails_responsables']=explode(";",html_entity_decode($db->result[0]['mails_responsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
    }elseif(is_array($id)){
      $ids=join(",",$id);
      $db=new db();
      $db->select2("personnel",null,array("id"=>"IN $ids"));
      if($db->result){
	foreach($db->result as $elem){
	  $this->elements[$elem['id']]=$elem;
	  $sites = json_decode(html_entity_decode($elem['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	  $this->elements[$elem['id']]['sites'] = $sites ? $sites : array();
	  $this->elements[$elem['id']]['mails_responsables']=explode(";",html_entity_decode($elem['mails_responsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	}
      }
    }
  }


  public function fetchEDTSamedi($perso_id,$debut,$fin){
    if(!$GLOBALS['config']['EDTSamedi'] or $GLOBALS['config']['PlanningHebdo']){
      return false;
    }
    $db=new db();
    $db->select("edt_samedi","*","semaine>='$debut' AND semaine<='$fin' AND perso_id='$perso_id'");
    if($db->result){
      foreach($db->result as $elem){
	$this->elements[]=$elem['semaine'];
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
  public function getICSCode($id){
    $this->fetchById($id);
    $code = $this->elements[0]['code_ics'];
    if(!$code){
      $code = md5(time().rand(100,999));
      $db = new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->update2('personnel', array('code_ics'=>$code), array('id'=>$id));
    }
    return $code;
  }
  
  /**
   * getICSURL
   * Retourne l'URL ICS de l'agent.
   * @param int $id : id de l'agent
   * @return string $url
   */
  public function getICSURL($id){
    $url = createURL();
    $url = str_replace('/index.php?page=', "/ics/calendar.php?id=$id", $url);
    if($GLOBALS['config']['ICS-Code']){
      $code = $this->getICSCode($id);
      $url .= "&amp;code=$code";
    }
    return $url;
  }

  public function update_time(){
    $db=new db();
    $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['config']['dbprefix']}personnel';");
    $result = isset($db->result[0]['Update_time']) ? $db->result[0]['Update_time'] : null;
    return $result;
  }
  
  public function updateEDTSamedi($eDTSamedi,$debut,$fin,$perso_id){
    if(!$GLOBALS['config']['EDTSamedi'] or $GLOBALS['config']['PlanningHebdo']){
      return false;
    }

    $db=new db();
    $db->CSRFToken = $this->CSRFToken;
    $db->delete2("edt_samedi", array('semaine' => ">=$debut", 'semaine' => "<=$fin", 'perso_id' => $perso_id));

    if($eDTSamedi and !empty($eDTSamedi)){
      $insert=array();
      foreach($eDTSamedi as $elem){
	$insert[]=array("perso_id"=>$perso_id, "semaine"=>$elem);
      }
      $db=new db();
      $db->CSRFToken = $this->CSRFToken;
      $db->insert2("edt_samedi",$insert);
    }
 }

}

?>