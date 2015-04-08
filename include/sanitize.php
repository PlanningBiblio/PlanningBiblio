<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/sanitize.php
Création : 7 avril 2015
Dernière modification : 8 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Page contenant les fonctions PHP de nettoyages de variables
Page appelée par les fichiers index.php, et authentification.php
*/

// pas de $version=acces direct au fichier => Accès refusé
if(!isset($version)){
  include_once "accessDenied.php";
}

/**
 * Sanitizes ldap search strings.
 * See rfc2254
 * @link http://www.faqs.org/rfcs/rfc2254.html
 * @since 1.5.1 and 1.4.5
 * @param string $string
 * @return string sanitized string
 * @author Squirrelmail Team
 */
function ldapspecialchars($string) {
    $sanitized=array('\\' => '\5c',
                     '*' => '\2a',
                     '(' => '\28',
                     ')' => '\29',
                     "\x00" => '\00');

    return str_replace(array_keys($sanitized),array_values($sanitized),$string);
}

function sanitize_array_string($n){
 if(is_array($n)){
    return array_map("sanitize_array_string",$n);
  }
  return filter_var($n,FILTER_SANITIZE_STRING);
}

function sanitize_array_unsafe($n){
 if(is_array($n)){
    return array_map("sanitize_array_unsafe",$n);
  }
  return filter_var($n,FILTER_UNSAFE_RAW);
}

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

function sanitize_file_extension($input){
  $reponse_filtre = null;
  $extensions=array("xls","csv","pdf");
  if(in_array($input,$extensions)){
    $reponse_filtre = $input;
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
    $reponse_filtre = true;
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