<?php
/**
Planning Biblio, Version 2.4.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/poste/supprimer.php
Création : mai 2011
Dernière modification : 3 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de supprimer un planning. Demande si l'on veut supprimer le jour ou la semaine entière.
Confirmation est suppression des informations dans la base de données

Cette page est appelée par la fonction JavaScript "popup" qui affiche cette page dans un cadre flottant lors du click sur
l'icône "Suppression" de la page planning/poste/index.php
*/

require_once "class.planning.php";

// Initialisation des variables
$confirm=filter_input(INPUT_GET,"confirm",FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_GET,"date",FILTER_SANITIZE_STRING);
$semaineJour=filter_input(INPUT_GET,"semaineJour",FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);

$confirm=filter_var($confirm,FILTER_CALLBACK,array("options"=>"sanitize_on"));
$date=filter_var($date,FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));

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

if(!$semaineJour){		// Etape 1 : Suppression du jour ou de la semaine ?
  echo "Voulez vous supprimer le planning du jour ($dateFr)<br/>ou de la semaine (du $debutFr au $finFr) ?<br/><br/>\n";
  echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;date=$date&amp;site=$site&amp;semaineJour=jour'>Jour</a>&nbsp;&nbsp;&nbsp;\n";
  echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;date=$date&amp;site=$site&amp;semaineJour=semaine'>Semaine</a><br/><br/>\n";
  echo "<a href='javascript:popup_closed();'>Annuler</a>\n";
}
elseif(!$confirm){		// Etape 2 : Demande confirmation
  if($semaineJour=="semaine"){		// confirmation pour la semaine
    echo "Etes vous sûr de vouloir supprimer le planning de la semaine<br/>(du $debutFr au $finFr) ?<br/><br/>\n";
    echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;date=$date&amp;site=$site&amp;semaineJour=semaine&amp;confirm=on'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
    echo "<a href='javascript:popup_closed();'>Non</a>\n";
  }
  else{									// confirmation pour le jour
    echo "Etes vous sûr de vouloir supprimer du $dateFr ?<br/><br/>\n";
    echo "<a href='index.php?page=planning/poste/supprimer.php&amp;menu=off&amp;date=$date&amp;site=$site&amp;semaineJour=jour&amp;confirm=on'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
    echo "<a href='javascript:popup_closed();'>Non</a>\n";
  }
}
else{
  // Suppression de la semaine
  if($semaineJour=="semaine"){
    // Table pl_poste (affectation des agents)
    $db=new db();
    $db->delete2("pl_poste",array("site"=>$site, "date"=>"BETWEEN{$debut}AND{$fin}"));

    // Table pl_poste_tab_affect (affectation des tableaux)
    $db=new db();
    $db->delete2("pl_poste_tab_affect",array("site"=>$site, "date"=>"BETWEEN{$debut}AND{$fin}"));
  }
  // Suppression du jour
  else{
    // Table pl_poste (affectation des agents)
    $db=new db();
    $db->delete2("pl_poste",array("site"=>$site, "date"=>$date));

    // Table pl_poste_tab_affect (affectation des tableaux)
    $db=new db();
    $db->delete2("pl_poste_tab_affect",array("site"=>$site, "date"=>$date));
  }
  echo "<script type='text/JavaScript'>top.document.location.href=\"index.php\";</script>\n";
}
?>
</div>