<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.7
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : planning/postes_cfg/class.tableaux.php										*
* Création : mai 2011														*
* Dernière modification : 07 décembre 2012											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Classe tableau : classe permettant de manipuler les tableaux (recherche, insertion, modification, groupe)			*
*																*
* Utilisée par les fichiers du dossier "planning/postes_cfg" et le fichier "planning/poste/index.php"				*
*********************************************************************************************************************************/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version and !strpos($_SERVER['SCRIPT_NAME'],"groupes_supp.php")){
  header("Location: ../../index.php");
}

class tableau{
  public $elements=array();
  
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