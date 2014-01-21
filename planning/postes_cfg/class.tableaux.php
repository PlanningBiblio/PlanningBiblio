<?php
/*
Planning Biblio, Version 1.6.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/class.tableaux.php
Création : mai 2011
Dernière modification : 21 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe tableau : classe permettant de manipuler les tableaux (recherche, insertion, modification, groupe)

Utilisée par les fichiers du dossier "planning/postes_cfg" et le fichier "planning/poste/index.php"
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version and !strpos($_SERVER['SCRIPT_NAME'],"groupes_supp.php")){
  header("Location: ../../index.php");
}

class tableau{
  public $elements=array();
  public $id=null;
  public $length=null;
  public $next=null;
  public $numbers=null;

  public function deleteGroup($id){
    $db=new db();
    $db->delete("pl_poste_tab_grp","`id`='$id'");
  }

  public function fetchAll(){
    $db=new db();
    $db->select("pl_poste_tab");
    $tab=$db->result;
    if(is_array($tab)){
      usort($tab,"cmp_nom");
    }
    $this->elements=$tab;
  }

  public function fetchAllGroups(){
    $db=new db();
    $db->select("pl_poste_tab_grp");
    $tab=$db->result;
    if(is_array($tab)){
      usort($tab,"cmp_nom");
    }
    $this->elements=$tab;
  }

  public function fetchGroup($id){
    $db=new db();
    $db->select("pl_poste_tab_grp","*","`id`='$id'");
    $this->elements=$db->result[0];
  }

  public  function getNumbers(){
    $db=new db();
    $db->select("pl_poste_horaires","tableau","numero='{$this->id}'","group by tableau");
    if(!$db->result){
      return;
    }

    $numbers=array();
    foreach($db->result as $elem){
      $numbers[]=$elem['tableau'];
    }
    $length=count($numbers);
    sort($numbers);
    $next=$numbers[$length-1]+1;
    
    $this->length=$length;
    $this->next=$next;
    $this->numbers=$numbers;
  }

  public  function setNumbers($number){
    $this->getNumbers();
    $length=$this->length;
    $next=$this->next;
    $numbers=$this->numbers;
    $id=$this->id;

    $diff=intval($number)-intval($length);
    if($diff==0){
      return;
    }

    if($diff>0){
      for($i=$next;$i<($diff+$next);$i++){
	$horaires=array("debut"=>"09:00:00","fin"=>"10:00:00","tableau"=>$i,"numero"=>$id);
	$db=new db();
	$db->insert2("pl_poste_horaires",$horaires);

	$lignes=array("ligne"=>0,"poste"=>0,"type"=>"poste","tableau"=>$i,"numero"=>$id);
	$db=new db();
	$db->insert2("pl_poste_lignes",$lignes);
      }
    }

    if($diff<0){
      $i=$number;
      while($numbers[$i]){
	$db=new db();
	$db->delete("pl_poste_horaires","tableau='{$numbers[$i]}' AND numero=$id");
	$db=new db();
	$db->delete("pl_poste_lignes","tableau='{$numbers[$i]}' AND numero=$id");
	$i++;
      }
    }
  }

  public function update($post){
    //		Update
    $post['nom']=trim($post['nom']);
    if($post["id"]){
      $db=new db();
      $db->update2("pl_poste_tab_grp",$post,array("id"=>$post['id']));
    }
    //		Insert
    else{
      $db=new db();
      $db->insert2("pl_poste_tab_grp",$post);
    }
  }
}
?>