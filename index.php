<?php
/**
Planning Biblio, Version 2.8.03
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : index.php
Création : mai 2011
Dernière modification : 29 mai 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Page principale,
Vérifie si la base de données doit être mise à jour, inclus les pages de configuration et
de fonctions communes, vérifie les droits à la page demandée en argument et l'inclus si l'utilisateur
est autorisé à la consulter

Inclut au départ les fichiers config.php, doctype.php et header.php
Inclut à la fin le fichier footer.php
*/

include_once('init.php');

// Vérification de la version de la base de données
// Si la version est différente, mise à jour de la base de données 
if($version!=$config['Version']){
  include "setup/maj.php";
}
// Sinon, on continue
else{
  include "include/feries.php";
  include "plugins/plugins.php";
  if(isset($_SESSION['login_id'])){
    include "include/cron.php";
  }
}

// Si pas de session, redirection vers la page d'authentification
if(!array_key_exists("login_id",$_SESSION)){
  // Action executée dans un popup alors que la session a été perdue, on affiche
  if(!$menu){
    echo "<div style='margin:60px 30px;'>\n";
    echo "<center>\n";
    echo "Votre session a expiré.<br/><br/>\n";
    echo "<a href='authentification.php' target='_top'>Cliquez ici pour vous reconnecter</a>\n";
    echo "<center></div>\n";
    exit;
  }else{
    // Session perdue, on affiche la page d'authentification
    $redirURL="index.php?".$_SERVER['QUERY_STRING'];
    include_once "authentification.php";
    exit;
  }
}

include "include/header.php";
if($menu){
  include "include/menu.php";
}

// Sécurité CSRFToken
echo <<<EOD
<form name='CSRFForm' action='#' method='get'>
<input type='hidden' name='CSRFSession' id='CSRFSession' value='$CSRFSession' />
</form>
EOD;

//		Recupération des droits d'accès de l'agent
$db=new db();
$db->select2("personnel","droits",array("id"=>$_SESSION['login_id']));
$droits=json_decode(html_entity_decode($db->result[0]['droits'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
$droits[]=99;	// Ajout du droit de consultation pour les connexions anonymes
$_SESSION['droits']=$droits;

if($page=="planning/poste/index.php" or $page=="planning/poste/semaine.php" or !$menu){
  echo "<div id='content-planning'>\n";
}else{
  echo "<div id='content'>\n";
}

//		Droits necessaires pour consulter la page en cours
$db=new db();
$db->select2("acces","*",array("page"=>$page));

$access = false;
if($db->result){
  foreach($db->result as $elem){
    if(in_array($elem['groupe_id'], $droits)){
      $access = true;
      break;
    }
  }
}

if($access){
  include $page;
}
else{
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
}
if($menu){
  include "include/footer.php";
}
?>