<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/sanitize.php
Création : 7 avril 2015
Dernière modification : 7 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Page contenant les fonctions PHP de nettoyages de variables
Page appelée par les fichiers index.php, et authentification.php
*/

function sanitize_dateFr($input){
  $reponse_filtre = null;
  // Vérifions si le format est valide
  if(preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $input, $matches)){
    // Vérifions si la date existe
    if(checkdate($matches[2], $matches[1], $matches[3])){
      $reponse_filtre = $input;
    }
  }
  return $reponse_filtre;
}

function sanitize_dateSQL($input){
  $reponse_filtre = null;
  // Vérifions si le format est valide
  if(preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $input, $matches)){
    // Vérifions si la date existe
//     if(checkdate($matches[2], $matches[3], $matches[1])){
      $reponse_filtre = $input;
//     }
  }
  return $reponse_filtre;
}

// sanitize_heure retourne "00:00:00" par défaut
function sanitize_heure($input){
  $reponse_filtre = "00:00:00";
  // Vérifions si le format est valide
  if(preg_match('#^(\d{1,2}):(\d{2}):(\d{2})$#', $input, $matches)){
    $reponse_filtre = $input;
  }
  return $reponse_filtre;
}

// sanitize_heure_fin retourne "23:59:59" par défaut
function sanitize_heure_fin($input){
  $reponse_filtre = "23:59:59";
  // Vérifions si le format est valide
  if(preg_match('#^(\d{1,2}):(\d{2}):(\d{2})$#', $input, $matches)){
    $reponse_filtre = $input;
  }
  return $reponse_filtre;
}

// sanitize_on retourne false par défaut
// Permet par exemple de controler les checkboxes
function sanitize_on($input){
  $reponse_filtre = false;
  // Vérifions si le format est valide
  if($input){
    $reponse_filtre = $input;
  }
  return $reponse_filtre;
}

// sanitize_on01 retourne 0 par défaut, sinon 1
// Permet par exemple de controler les checkboxes
function sanitize_on01($input){
  $reponse_filtre = 0;
  // Vérifions si le format est valide
  if($input){
    $reponse_filtre = 1;
  }
  return $reponse_filtre;
}

// sanitize_page
// Contrôle si la page demandée peut être chargée
function sanitize_page($input){
  $reponse_filtre = null;
  $input=filter_var($input,FILTER_SANITIZE_STRING);
  if($input){
    $db=new db();
    $db->select2("acces","page",array("page"=>$input));
    if($db->result){
      $reponse_filtre = $input;
    }
  }
  return $reponse_filtre;
}
?>