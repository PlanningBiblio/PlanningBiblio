<?php
/**
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : plugins/planningHebdo/valid.php
Création : 23 juillet 2013
Dernière modification : 19 juin 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de valider la saisie de son planning de présence hebdomadaire
*/

require_once "class.planningHebdo.php";

// Initialisation des variables
$post=filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);

switch($post["action"]){
  case "ajout" :
    $p=new planningHebdo();
    $p->add($post);
    if($p->error){
      $msg=urlencode("Une erreur est survenue lors de l'enregistrement du planning.");

      if($post['id']){
	$msg=urlencode("Une erreur est survenue lors de la copie du planning.");
      }

      $msgType="error";    	

    }else{
      $msg=urlencode("Le planning a été ajouté avec succés.");
      if($post['id']){
	$msg=urlencode("Le planning a été copié avec succés.");
      }
      $msgType="success";    	
    }
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planningHebdo/{$post['retour']}&msg=$msg&msgType=$msgType';</script>\n";
    break;

  case "modif" :
    $p=new planningHebdo();
    $p->update($post);
    if($p->error){
    	$msg=urlencode("Une erreur est survenue lors de la modification du planning.");
    	$msgType="error";    	
    }else{
    	$msg=urlencode("Le planning a été modifié avec succés.");
    	$msgType="success";    	
    }
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planningHebdo/{$post['retour']}&msg=$msg&msgType=$msgType';</script>\n";
    break;
 
  case "copie" :
    $p=new planningHebdo();
    $p->copy($post);
    if($p->error){
    	$msg=urlencode("Une erreur est survenue lors de la modification du planning.");
    	$msgType="error";    	
    }else{
    	$msg=urlencode("Le planning a été modifié avec succés.");
    	$msgType="success";    	
    }
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=planningHebdo/{$post['retour']}&msg=$msg&msgType=$msgType';</script>\n";
    break;
}
?>