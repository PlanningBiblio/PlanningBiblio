<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/supprimer.php
Création : mai 2011
Dernière modification : 19 juillet 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de supprimer un planning. Demande si l'on veut supprimer le jour ou la semaine entière.
Confirmation est suppression des informations dans la base de données

Cette page est appelée par la fonction JavaScript "popup" qui affiche cette page dans un cadre flottant lors du click sur
l'icône "Suppression" de la page planning/poste/index.php
*/

require_once "class.planning.php";


// Initialisation des variables
$date=$_SESSION['PLdate'];
$site=$_SESSION['oups']['site'];
$dateFr=dateFr($date);
$d=new datePl($date);
$debut=$d->dates[0];
$fin=$d->dates[6];
$debutFr=dateFr($debut);
$finFr=dateFr($fin);

// Sécurité
// Refuser l'accès aux agents n'ayant pas les droits de modifier le planning
$access=true;
$droit=($config['Multisites-nombre']>1)?(300+$site):12;
if(!in_array($droit,$droits)){
  echo "<div id='acces_refuse'>Accès refusé</div>";
  echo "<a href='javascript:popup_closed();'>Fermer</a>\n";
  exit;
}


echo "<div style='text-align:center'>\n";
echo "<br/>\n";

if(!isset($_GET['semaineJour'])){		// Etape 1 : Suppression du jour ou de la semaine ?
  echo "Voulez vous supprimer le planning du jour ($dateFr)<br/>ou de la semaine (du $debutFr au $finFr) ?<br/><br/>\n";
  echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;semaineJour=jour'>Jour</a>&nbsp;&nbsp;&nbsp;\n";
  echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;semaineJour=semaine'>Semaine</a><br/><br/>\n";
  echo "<a href='javascript:popup_closed();'>Annuler</a>\n";
}
elseif(!isset($_GET['confirm'])){		// Etape 2 : Demande confirmation
  if($_GET['semaineJour']=="semaine"){		// confirmation pour la semaine
    echo "Etes vous sûr de vouloir supprimer le planning de la semaine<br/>(du $debutFr au $finFr) ?<br/><br/>\n";
    echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;semaineJour=semaine&amp;confirm=on'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
    echo "<a href='javascript:popup_closed();'>Non</a>\n";
  }
  else{									// confirmation pour le jour
    echo "Etes vous sûr de vouloir supprimer du $dateFr ?<br/><br/>\n";
    echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;semaineJour=jour&amp;confirm=on'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
    echo "<a href='javascript:popup_closed();'>Non</a>\n";
  }
}
else{
  if($_GET['semaineJour']=="semaine"){		// suppression de la semaine
    $req[]="DELETE FROM `{$dbprefix}pl_poste` WHERE `site`='$site' AND `date` BETWEEN '$debut' AND '$fin';";
    $req[]="DELETE FROM `{$dbprefix}pl_poste_tab_affect` WHERE `site`='$site' AND `date` BETWEEN '$debut' AND '$fin';";
  }
  else{						// suppression du jour
    $req[]="DELETE FROM `{$dbprefix}pl_poste` WHERE `site`='$site' AND `date`='$date';";
    $req[]="DELETE FROM `{$dbprefix}pl_poste_tab_affect` WHERE `site`='$site' AND `date`='$date';";
  }
  $db=new db();
  $db->query($req[0]);
  $db=new db();
  $db->query($req[1]);
  echo "<script type='text/JavaScript'>top.document.location.href=\"index.php\";</script>\n";
}
?>
</div>