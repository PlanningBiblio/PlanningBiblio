<?php
/**
Planning Biblio, Version 2.6.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planningHebdo/class.planningHebdo.php
Création : 23 juillet 2013
Dernière modification : 21 mai 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant le fonctions planningHebdo.
Appelé par les autres fichiers du dossier planningHebdo
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once __DIR__."/../include/accessDenied.php";
}

require_once __DIR__."/../personnel/class.personnel.php";

class planningHebdo{
  public $agent=null;
  public $config=array();
  public $dates=array();
  public $debut=null;
  public $elements=array();
  public $error=null;
  public $fin=null;
  public $id=null;
  public $ignoreActuels=null;
  public $periodes=null;
  public $perso_id=null;
  public $tri=null;
  public $valide=null;


  public function __construct(){
  }

  public function add($data){
    // Modification du format des dates de début et de fin si elles sont en français
    if(array_key_exists("debut",$data)){
      $data['debut']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$3-$2-$1",$data['debut']);
      $data['fin']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$3-$2-$1",$data['fin']);
    }

    $perso_id=array_key_exists("perso_id",$data)?$data["perso_id"]:$_SESSION['login_id'];

    // Validation
    // Par défaut = 0, si $data['validation'] valide = login de l'agent logué, validation = date courante

    $valide=0;
    $validation="0000-00-00 00:00:00";
    if(array_key_exists("validation",$data) and $data['validation']){
      $valide=$_SESSION['login_id'];
      $validation=date("Y-m-d H:i:s");
    }

    $CSRFToken=$data['CSRFToken'];
    unset($data['CSRFToken']);

    // Si $data['annee'] : il y a 2 périodes distinctes avec des horaires définis 
    // (horaires normaux et horaires réduits) soit 2 tableaux à insérer
    if(array_key_exists("annee",$data)){
      // Récupération des horaires
      $this->dates=array($data['annee']);
      $this->getPeriodes();
      $dates=$this->periodes;

      // 1er tableau
      $insert=array("perso_id"=>$perso_id,"debut"=>$dates[0][0],"fin"=>$dates[0][1],"temps"=>json_encode($data['temps']), 
	"valide"=>$valide, "validation"=>$validation);

      $db=new db();
      $db->insert2("planning_hebdo",$insert);
      $this->error=$db->error;
      // 2ème tableau
      $insert=array("perso_id"=>$perso_id,"debut"=>$dates[0][2],"fin"=>$dates[0][3],"temps"=>json_encode($data['temps2']),
	"valide"=>$valide, "validation"=>$validation);

      $db=new db();
      $db->insert2("planning_hebdo",$insert);
      $this->error=$db->error?$db->error:$this->error;
    }
    // Sinon, insertion d'un seul tableau
    else{
      $insert=array("perso_id"=>$perso_id,"debut"=>$data['debut'],"fin"=>$data['fin'],"temps"=>json_encode($data['temps']), 
	"valide"=>$valide, "validation"=>$validation);

      // Dans le cas d'une copie (voir fonction copy)
      if(isset($data['remplace'])){
	$insert['remplace']=$data['remplace'];
      }
      $db=new db();
      $db->insert2("planning_hebdo",$insert);
      $this->error=$db->error;
    }

    // Envoi d'un mail aux responsables
    $destinataires=array();
    if($GLOBALS['config']['PlanningHebdo-Notifications']=="droit"){
      $p=new personnel();
      $p->fetch("nom");
      foreach($p->elements as $elem){
	$tmp=json_decode(html_entity_decode($elem['droits'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	if(in_array(24,$tmp)){
	  $destinataires[]=$elem['mail'];
	}
      }
    }
    elseif($GLOBALS['config']['PlanningHebdo-Notifications']=="Mail-Planning"){
      $destinataires=explode(";",$GLOBALS['config']['Mail-Planning']);
    }

    if(!empty($destinataires)){
      $nomAgent = nom($perso_id,"prenom nom");
      $sujet="Nouveau planning de présence, ".html_entity_decode($nomAgent,ENT_QUOTES|ENT_IGNORE,"UTF-8");
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

  public function copy($data){
    // Modification du format des dates de début et de fin si elles sont en français
    $data['debut']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$3-$2-$1",$data['debut']);
    $data['fin']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$3-$2-$1",$data['fin']);

    $CSRFToken=$data['CSRFToken'];
    unset($data['CSRFToken']);

    $this->id=$data['id'];
    $this->fetch();
    $actuel=$this->elements[0];

    // Copie de l'ancien planning avec modification des dates de début et/ou de fin
    $pl=array();
    // Copie de l'ancien planning
    $pl[0]=$actuel;
    $pl[0]['remplace']=$actuel['id'];

    // Modification de la date de fin de la copie et création d'une 2ème copie si les 2 dates sont modifiées
    if($data['debut']>$actuel['debut'] and $data['fin']<$actuel['fin']){
      $pl[0]['fin']=date("Y-m-d",strtotime("-1 Day",strtotime($data['debut'])));
      $pl[1]=$actuel;
      $pl[1]['debut']=date("Y-m-d",strtotime("+1 Day",strtotime($data['fin'])));
      $pl[1]['remplace']=$actuel['id'];
    }
    // Modification de la date de fin de la copie si la date de début est modifiée
    elseif($data['debut']>$actuel['debut']){
      $pl[0]['fin']=date("Y-m-d",strtotime("-1 Day",strtotime($data['debut'])));
    }
    // Modification de la date de début de la copie si la date de fin est modifiée
    elseif($data['fin']<$actuel['fin']){
      $pl[0]['debut']=date("Y-m-d",strtotime("+1 Day",strtotime($data['fin'])));
    }

    // Enregistrement des copies
    foreach($pl as $elem){
      $p=new planningHebdo();
      $p->add($elem);
    }
    
    // Enregistrement du nouveau planning
    $data['remplace']=$actuel['id'];
    $p=new planningHebdo();
    $p->add($data);
  }
  
  public function fetch(){
    // Recherche des services
    $p=new personnel();
    $p->fetch();
    foreach($p->elements as $elem){
      $services[$elem['id']]=$elem['service'];
    }

    // Filtre de recherche
    $filter="1";

    // Perso_id
    if($this->perso_id){
      $filter.=" AND `perso_id`='{$this->perso_id}'";
    }

    // Date, debut, fin
    $debut=$this->debut;
    $fin=$this->fin;
    $date=date("Y-m-d");
    if($debut){
      $fin=$fin?$fin:$date;
      $filter.=" AND `debut`<='$fin' AND `fin`>='$debut'";
    }
    else{
      $filter.=" AND `fin`>='$date'";
    }


    // Recherche des agents actifs seulement
    $perso_ids=array(0);
    $p=new personnel();
    $p->fetch("nom");
    foreach($p->elements as $elem){
      $perso_ids[]=$elem['id'];
    }

    // Recherche avec le nom de l'agent
    if($this->agent){
      $perso_ids=array(0);
      $p=new personnel();
      $p->fetch("nom",null,$this->agent);
      foreach($p->elements as $elem){
	$perso_ids[]=$elem['id'];
      }
    }

    // Filtre pour agents actifs seulement et recherche avec nom de l'agent
    $perso_ids=join(",",$perso_ids);
    $filter.=" AND `perso_id` IN ($perso_ids)";

    // Valide
    if($this->valide){
      $filter.=" AND `valide`<>0";
    }
  
    // Ignore actuels (pour l'import)
    if($this->ignoreActuels){
      $filter.=" AND `actuel`=0";
    }
  
    // Filtre avec ID, si ID, les autres filtres sont effacés
    if($this->id){
      $filter="`id`='{$this->id}'";
    }

    $db=new db();
    $db->select("planning_hebdo","*",$filter,"ORDER BY debut,fin,saisie");
    
    $p=new personnel();
    $p->supprime = array(0,1,2);
    $p->fetch();
    $agents = $p->elements;
    

    if($db->result){
      foreach($db->result as $elem){
	$elem['temps'] = json_decode(html_entity_decode($elem['temps'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	$elem['nom'] = nom($elem['perso_id'],'nom p',$agents);
	$elem['service']=$services[$elem['perso_id']];
	$this->elements[]=$elem;
      }
    }

    // Tri par date de début, fin et nom des agents
    usort($this->elements,"cmp_debut_fin_nom");

    // Classe les plannings copiés (remplaçant) après les plannings d'origine
    $tab=array();
    foreach($this->elements as $elem){
      if(!$elem['remplace']){
	$tab[]=$elem;
	foreach($this->elements as $elem2){
	  if($elem2['remplace']==$elem['id']){
	    $tab[]=$elem2;
	  }
	}
      }
    }

    // $tab est vide si on accède directement à un planning copié,
    // on remplace donc $this->elements par $tab seulement si $tab n'est pas vide.
    if(!empty($tab)){
      $this->elements=$tab;
    }

  }

  public function getPeriodes(){
    if(!empty($this->dates)){
      $dates=array();
      $annees=$this->dates;
      sort($annees);
      $i=0;
      foreach($annees as $annee){
	$db=new db();
	$db->select("planning_hebdo_periodes","*","`annee`='$annee'","ORDER BY `annee`");
	if($db->result){
	  $dates[$i]=json_decode(html_entity_decode($db->result[0]['dates'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	  $datesFr[$i]=array_map("dateFr",$dates[$i]);
	  $i++;
	}
	else{
	  $dates[$i]=null;
	  $datesFr[$i]=null;
	  $i++;
	}
      }
    }
  $this->periodes=$dates;
  $this->periodesFr=$datesFr;
  }

  public function suppression_agents($liste){
    $db=new db();
    $db->delete("planning_hebdo","perso_id IN ($liste)");
  }

  public function update($data){
    // Modification du format des dates de début et de fin si elles sont en français
    $data['debut']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$3-$2-$1",$data['debut']);
    $data['fin']=preg_replace("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$3-$2-$1",$data['fin']);

    $perso_id=array_key_exists("valide",$data)?$data["valide"]:$_SESSION['login_id'];

    $temps=json_encode($data['temps']);
    $update=array("debut"=>$data['debut'],"fin"=>$data['fin'],"temps"=>$temps,"modif"=>$perso_id,"modification"=>date("Y-m-d H:i:s"));
    if($data['validation']){
      $update['valide']=$perso_id;
      $update['validation']=date("Y-m-d H:i:s");
    }
    
    $CSRFToken=$data['CSRFToken'];
    unset($data['CSRFToken']);

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update2("planning_hebdo",$update,array("id"=>$data['id']));
    $this->error=$db->error;

    // Remplacement du planning de la fiche agent si validation et date courante entre debut et fin
    if($data['validation'] and $data['debut']<=date("Y-m-d") and $data['fin']>=date("Y-m-d")){
      $db=new db();
      $db->update("planning_hebdo","`actuel`='0'","`perso_id`='{$data['perso_id']}'");
      $db=new db();
      $db->update("planning_hebdo","`actuel`='1'","`id`='{$data['id']}'");
    }

    // Si validation d'un planning de remplacement, suppression du planning d'origine
    if($data['validation'] and $data['remplace']){
      $db=new db();
      $db->delete("planning_hebdo","id='{$data['remplace']}'");
      $db=new db();
      $db->update("planning_hebdo","remplace='0'","remplace='{$data['remplace']}'");
    }

    // Envoi d'un mail aux responsables et à l'agent concerné
    $destinataires=array();

    // Les admins
    if($GLOBALS['config']['PlanningHebdo-Notifications']=="droit"){
      $p=new personnel();
      $p->fetch("nom");
      foreach($p->elements as $elem){
	$tmp=json_decode(html_entity_decode($elem['droits'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
	if(in_array(24,$tmp)){
	  $destinataires[]=$elem['mail'];
	}
      }
    }
    elseif($GLOBALS['config']['PlanningHebdo-Notifications']=="Mail-Planning"){
      $destinataires=explode(";",$GLOBALS['config']['Mail-Planning']);
    }
    // L'agent
    $p=new personnel();
    $p->fetchById($data['perso_id']);
    $destinataires[]=$p->elements[0]['mail'];

    $nomAgent = nom($data['perso_id'],"prenom nom");

    if(!empty($destinataires)){
      if($data['validation']){
	$sujet="Validation d'un planning de présence, ".html_entity_decode($nomAgent,ENT_QUOTES|ENT_IGNORE,"UTF-8");
	$message="Un planning de présence de ";
	$message.=$nomAgent;
	$message.=" a été validé dans l'application Planning Biblio<br/>";
      }
      else{
	$sujet="Modification d'un planning de présence, ".html_entity_decode($nomAgent,ENT_QUOTES|ENT_IGNORE,"UTF-8");
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
  
  public function update_time(){
    $db=new db();
    $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['config']['dbprefix']}planning_hebdo';");
    $result = isset($db->result[0]['Update_time']) ? $db->result[0]['Update_time'] : null;
    return $result;

  }
  
  public function updatePeriodes($data){
    $annee=array($data['annee'][0],$data['annee'][1]);
    // Convertion des dates JJ/MM/AAAA => AAAA-MM-JJ
    $data['dates'][0]=array_map("dateFr",$data['dates'][0]);
    $data['dates'][1]=array_map("dateFr",$data['dates'][1]);
    $dates=array(json_encode($data['dates'][0]),json_encode($data['dates'][1]));

    for($i=0;$i<count($annee);$i++){
      $db=new db();
      $db->delete("planning_hebdo_periodes","`annee`='{$annee[$i]}'");
      $this->error=$db->error?true:false;
      $insert=array("annee"=>$annee[$i],"dates"=>$dates[$i]);
      $db=new db();
      $db->insert2("planning_hebdo_periodes",$insert);
      $this->error=$db->error?true:$this->error;
    }
  }
    
}

?>