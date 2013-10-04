<?php
/*
Planning Biblio, Version 1.5.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : planning/poste/verrou.php
Création : mai 2011
Dernière modification : 19 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de verrouiller (et de déverrouiller) le planning du jour courant pour en interdire la modification et le rendre 
visible aux agents n'ayant pas le droit de modifier les plannings

Page appelée par le fichier index.php lors du click sur le cadenas de la page planning/poste/index.php
*/

require_once "class.planning.php";

// Initialisation des variables
$date=$_GET['date'];
$site=$_GET['site'];
$verrou=isset($_GET['verrou'])?$_GET['verrou']:null;
$verrou2=$_GET['verrou2'];
$d=new datePl($date);
$d1=$d->dates[0];
$perso_id=$_SESSION['login_id'];

// Sécurité
// Refuser l'accès aux agents n'ayant pas les droits de modifier le planning
$access=true;
$droit=($config['Multisites-nombre']>1)?(300+$site):12;
if(!in_array($droit,$droits)){
  echo "<div id='acces_refuse'>Accès refusé</div>";
  include "include/footer.php";
  exit;
}

$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_verrou` WHERE `date`='$date' AND `site`='$site';");
if($db->result){
  if(isset($_GET['verrou']))
    $req="UPDATE `{$dbprefix}pl_poste_verrou` set verrou='$verrou' , validation=SYSDATE() , `perso`='$perso_id' WHERE `date`='$date' AND `site`='$site'";
  elseif(isset($_GET['verrou2'])){
    if($verrou2==1)
      $req="UPDATE `{$dbprefix}pl_poste_verrou` set verrou2='$verrou2' , validation2=SYSDATE() , `perso2`='$perso_id'  WHERE `date`='$date' AND `site`='$site';";
    else
      $req="UPDATE `{$dbprefix}pl_poste_verrou` set verrou2='$verrou2' , `perso2`='$perso_id'  WHERE `date`='$date' AND `site`='$site';";
  }
}	
else{
  if(isset($_GET['verrou']))
    $req="INSERT into `{$dbprefix}pl_poste_verrou` (`date`,`verrou`,`validation`,`perso`,`site`) values ('$date','$verrou',SYSDATE(),'$perso_id','$site' );";
  elseif(isset($_GET['verrou2']))
    $req="INSERT into `{$dbprefix}pl_poste_verrou` (`date`,`verrou2`,`validation2`,`perso2`,`site`) values ('$date','$verrou2',SYSDATE(),'$perso_id','$site' );";
}
$db=new db();
$db->query($req);
?>
<script type='text/JavaScript'>history.back();</script>