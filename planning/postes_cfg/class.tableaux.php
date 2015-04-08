<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/class.tableaux.php
Création : mai 2011
Dernière modification : 8 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe tableau : classe permettant de manipuler les tableaux (recherche, insertion, modification, groupe)

Utilisée par les fichiers du dossier "planning/postes_cfg" et le fichier "planning/poste/index.php"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../../include/accessDenied.php";
}

class tableau{
  public $elements=array();
  public $id=null;
  public $length=null;
  public $next=null;
  public $number=null;
  public $numbers=null;

  public function deleteGroup(){
    if($this->id){
      $db=new db();
      $db->delete2("pl_poste_tab_grp",array("id"=>$this->id));
    }
  }

  public function deleteLine(){
    if($this->id){
      $db=new db();
      $db->delete2("lignes",array("id"=>$this->id));
    }
  }

  public function deleteTab(){
    if($this->number){
      $where=array("numero"=>$this->number);

      $db=new db();
      $db->delete2("pl_poste_horaires",$where);
      $db=new db();
      $db->delete2("pl_poste_cellules",$where);
      $db=new db();
      $db->delete2("pl_poste_lignes",$where);

      $where=array("tableau"=>$this->number);
      $db=new db();
      $db->delete2("pl_poste_tab",$where);
    }
  }

  public function fetchAll(){
    $db=new db();
    $db->select2("pl_poste_tab");
    $tab=$db->result;
    if(is_array($tab)){
      usort($tab,"cmp_nom");
    }
    $this->elements=$tab;
  }

  public function fetchAllGroups(){
    $db=new db();
    $db->select2("pl_poste_tab_grp");
    $tab=$db->result;
    if(is_array($tab)){
      usort($tab,"cmp_nom");
    }
    $this->elements=$tab;
  }

  public function fetchGroup($id){
    $db=new db();
    $db->select2("pl_poste_tab_grp","*","`id`='$id'");
    $this->elements=$db->result[0];
  }

  // Recherche tous les éléments d'un tableau pour l'afficher
  public function get(){
    $tableauNumero=$this->id;

    // Liste des tableaux
    $tableaux=array();
    $db=new db();
    $db->select2("pl_poste_horaires","tableau",array("numero"=>$tableauNumero),"GROUP BY `tableau`");
    if($db->result){
      foreach($db->result as $elem){
	$tableaux[]=$elem['tableau'];
      }
    }

    // Liste des horaires
    $db=new db();
    $db->select2("pl_poste_horaires","*",array("numero"=>$tableauNumero),"ORDER BY `tableau`,`debut`,`fin`");
    $horaires=$db->result;

    // Liste des lignes enregistrées
    $lignes=array();
    $db=new db();
    $db->select2("pl_poste_lignes","*",array("numero"=>$tableauNumero),"ORDER BY tableau,ligne");
    if($db->result){
      $lignes=$db->result;
    }

    $titres=array();
    foreach($lignes as $ligne){
      if($ligne['type']=='titre'){
	$titres[$ligne['tableau']]=$ligne['poste'];
      }
    }

    // Liste des cellules grises
    $db=new db();
    $db->select2("pl_poste_cellules","*",array("numero"=>$tableauNumero),"ORDER BY tableau,ligne,colonne");
    $cellules_grises=array();
    if($db->result){
      foreach($db->result as $elem){
	$cellules_grises[]=array("tableau"=>$elem['tableau'],"nom"=>"{$elem['ligne']}_{$elem['colonne']}");
      }
    }

    // Construction du grand tableau
    $tabs=array();
    foreach($tableaux as $elem){
      // Initilisation des sous-tableaux et noms des sous-tableaux
      $tabs[$elem]=array("nom"=>$elem,"titre"=>null,"horaires"=>array(),"lignes"=>array(),"cellules_grises"=>array());

      // Titres et lignes des sous-tableaux
      foreach($lignes as $ligne){
	// Titres
	if($ligne['tableau']==$elem and $ligne['type']=="titre"){
	  $tabs[$elem]['titre']=$ligne['poste'];
	// Lignes (postes et lignes de séparation
	}elseif($ligne['tableau']==$elem){
	  $tabs[$elem]['lignes'][]=$ligne;
	}
      }

      // Horaires des sous-tableaux
      foreach($horaires as $horaire){
	if($horaire['tableau']==$elem){
	  $tabs[$elem]['horaires'][]=array("debut"=>$horaire['debut'],"fin"=>$horaire['fin']);
	}
      }

      // Cellules Grises
      foreach($cellules_grises as $cellule){
	if($cellule['tableau']==$elem){
	  $tabs[$elem]['cellules_grises'][]=$cellule['nom'];
	}
      }
    }
    $this->elements=$tabs;
  }

  public  function getNumbers(){
    $db=new db();
    $db->select2("pl_poste_horaires","tableau",array("numero"=>$this->id),"group by tableau");
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
      }
    }

    if($diff<0){
      $i=$number;
      while($numbers[$i]){
	$db=new db();
	$db->delete2("pl_poste_horaires",array("tableau"=>$numbers[$i], "numero"=>$id));
	$db=new db();
	$db->delete2("pl_poste_lignes",array("tableau"=>$numbers[$i], "numero"=>$id));
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