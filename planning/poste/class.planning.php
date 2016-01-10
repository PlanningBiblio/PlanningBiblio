<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/class.planning.php
Création : 16 janvier 2013
Dernière modification : 9 janvier 2016
@author : Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe planning 

Utilisée par les fichiers du dossier "planning/poste"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../../include/accessDenied.php";
}


class planning{
  public $date=null;
  public $site=1;
  public $categorieA=false;
  public $elements=array();
  public $menudiv=null;
  public $notes=null;
  public $notesTextarea=null;
  public $validation=null;


  public function fetch(){
    if(!$this->date){
      return;
    }

    $db=new db();
    $db->select2("pl_poste","*",array("date"=>$this->date));
    if($db->result){
      $tab=array();
      foreach($db->result as $elem){
	$tab[$elem['id']]=$elem;
      }
      $this->elements=$tab;
    }
  }
  
  
  // Recherche les agents de catégorie A en fin de service
  public function finDeService(){
    $date=$this->date;
    $site=$this->site;

    // Sélection du tableau utilisé
    $db=new db();
    $db->select("pl_poste_tab_affect","tableau","date='$date' AND site='$site'");
    $tableau=$db->result[0]["tableau"];

    // Sélection de l'heure de fin
    $db=new db();
    $db->select("pl_poste_horaires","MAX(fin) AS maxFin","numero='$tableau'");
    $fin=$db->result[0]["maxFin"];

    // Sélection des agents en fin de service
    $perso_ids=array();
    $db=new db();
    $db->select("pl_poste","perso_id","fin='$fin' and site='$site' and `date`='$date' and supprime='0' and absent='0'");
    if($db->result){
      foreach($db->result as $elem){
	$perso_ids[]=$elem['perso_id'];
      }
    }
    if(empty($perso_ids)){
      return false;
    }
    $perso_ids=join(",",$perso_ids);

    // Sélection des statuts des agents en fin de service
    $statuts=array();
    $db=new db();
    $db->select("personnel","statut","id IN ($perso_ids)");
    if($db->result){
      foreach($db->result as $elem){
	if(in_array($elem['statut'],$statuts)){
	  continue;
	}
	$statuts[]=$elem['statut'];
      }
    }
    if(empty($statuts)){
      return false;
    }
    $statuts=join("','",$statuts);

    // Recherche des statuts de catégorie A parmis les statuts fournis
    $db=new db();
    $db->select("select_statuts","*","valeur IN ('$statuts') AND categorie='1'");
    if($db->result){
      $this->categorieA=true;
    }
  }

  // Affiche la liste des agents dans le menudiv
  public function menudivAfficheAgents($poste,$agents,$date,$debut,$fin,$deja,$stat,$nbAgents,$sr_init,$hide,$deuxSP,$motifExclusion){
    $msg_deja_place="&nbsp;<font class='red bold'>(DP)</font>";
    $msg_deuxSP="&nbsp;<font class='red bold'>(2 SP)</font>";
    $msg_SR="&nbsp;<font class='red bold'>(SR)</font>";
    $config=$GLOBALS['config'];
    $dbprefix=$config['dbprefix'];
    $d=new datePl($date);
    $j1=$d->dates[0];
    $j7=$d->dates[6];
    $semaine=$d->semaine;
    $semaine3=$d->semaine3;
    $site=$this->site;

    if($hide){
      $display="display:none;";
      $groupe_hide=null;
      $classTrListe="tr_liste";
    }else{
      $display=null;
      $groupe_hide="groupe_tab_hide();";
      $classTrListe=null;
    }

    $menudiv=null;
    
    // Calcul des heures de SP à effectuer pour tous les agents
    $heuresSP=calculHeuresSP($date);

    // Calcul des heures d'absences afin d'ajuster les heures de SP
    $a=new absences();
    $heuresAbsencesTab=$a->calculHeuresAbsences($date);

    if(is_array($agents))
    foreach($agents as $elem){
      // Heures hebdomadaires (heures à faire en SP)
      $heuresHebdo=$heuresSP[$elem['id']];
      $heuresHebdoTitle="Quota hebdomadaire";
      
      // Heures hebdomadaires avec prise en compte des absences
      if($config["Planning-Absences-Heures-Hebdo"] and array_key_exists($elem['id'],$heuresAbsencesTab)){
	$heuresAbsences=$heuresAbsencesTab[$elem['id']];
	if(is_numeric($heuresAbsences)){
	  if($heuresAbsences>0){
	    // On informe du pourcentage sur les heures d'absences
	    $pourcent=null;
	    if(strpos($elem["heuresHebdo"],"%") and $elem["heuresHebdo"]!="100%"){
	      $pourcent=" {$elem["heuresHebdo"]}";
	    }
	    
	    $heuresHebdoTitle="Quota hebdomadaire = $heuresHebdo - $heuresAbsences (Absences{$pourcent})";
	    $heuresHebdo=$heuresHebdo-$heuresAbsences;
	    if($heuresHebdo<0){
	      $heuresHebdo=0;
	    }
	  }
	}else{
	  $heuresHebdoTitle="Quota hebdomadaire : Erreur de calcul des heures d&apos;absences";
	  $heuresHebdo="Erreur";
	}
      }
      
      $hres_jour=0;
      $hres_sem=0;

      if(!$config['ClasseParService']){
	if($elem['id']==2){		// on retire l'utilisateur "tout le monde"
	  continue;
	}
      }

      $nom=htmlentities($elem['nom'],ENT_QUOTES|ENT_IGNORE,"utf-8");
      if($elem['prenom']){
	$nom.=" ".substr(htmlentities($elem['prenom'],ENT_QUOTES|ENT_IGNORE,"utf-8"),0,1).".";
      }

      //			----------------------		Sans repas		------------------------------------------//
      // $sr permet d'interdire l'ajout d'un agent sur une cellule déjà occupée si cela met l'agent en SR
      $sr=0;

      // Si sans repas, on ajoute (SR) à l'affichage
      if($this->sansRepas($date,$debut,$fin,$elem['id'])){
	$sr=1;
	$nom.=$msg_SR;
      }
	      
      //			----------------------		Déjà placés		-----------------------------------------------------//
      if($config['Planning-dejaPlace']){
	if(in_array($elem['id'],$deja)){	// Déjà placé pour ce poste
	  $nom.=$msg_deja_place;
	}
      }
      //			----------------------		FIN Déjà placés		-----------------------------------------------------//

      // Vérifie si l'agent fera 2 plages de service public de suite
      if($config['Alerte2SP']){
	if(in_array($elem['id'],$deuxSP)){
	  $nom.=$msg_deuxSP;
	}
      }

      // Motifs d'indisponibilité
      if(array_key_exists($elem['id'],$motifExclusion)){
	$nom.=" (".join(", ",$motifExclusion[$elem['id']]).")";
      }

      // affihage des heures faites ce jour + les heures de la cellule
      $db_heures = new db();
      $db_heures->selectInnerJoin(array("pl_poste","poste"),array("postes","id"),
	array("debut","fin"),
	array(),
	array("perso_id"=>$elem['id'], "absent"=>"<>1", "date"=>$date),
	array("statistiques"=>"1"));

      if($stat){ 	// vérifier si le poste est compté dans les stats
	$hres_jour=diff_heures($debut,$fin,"decimal");
      }
      if($db_heures->result){
	foreach($db_heures->result as $hres){
	  $hres_jour=$hres_jour+diff_heures($hres['debut'],$hres['fin'],"decimal");
	}
      }
      
      // affihage des heures faites cette semaine + les heures de la cellule
      $db_heures = new db();
      $db_heures->selectInnerJoin(array("pl_poste","poste"),array("postes","id"),
	array("debut","fin"),array(),
	array("perso_id"=>$elem['id'], "absent"=>"<>1", "date"=>"BETWEEN{$j1}AND{$j7}"),
	array("statistiques"=>"1"));

      if($stat){ 	// vérifier si le poste est compté dans les stats
	$hres_sem=diff_heures($debut,$fin,"decimal");
      }
      if($db_heures->result){
	foreach($db_heures->result as $hres){
	  $hres_sem=$hres_sem+diff_heures($hres['debut'],$hres['fin'],"decimal");
	}
      }

      // affihage des heures faites les 4 dernières semaines + les heures de la cellule
      $hres_4sem=null;
      if($config['hres4semaines']){
	$hres_4sem=0;
	$date1=date("Y-m-d",strtotime("-3 weeks",strtotime($j1)));
	$date2=$j7;	// fin de semaine courante
	$db_hres4 = new db();
	$db_hres4->selectInnerJoin(array("pl_poste","poste"), array("postes","id"), array("debut","fin"), array(),
	  array("perso_id"=>$elem['id'], "absent"=>"<>1", "date"=>"BETWEEN{$date1}AND{$date2}"),
	  array("statistiques"=>"1"));

	if($stat){ 	// vérifier si le poste est compté dans les stats
	  $hres_4sem=diff_heures($debut,$fin,"decimal");
	}
	if($db_hres4->result){
	  foreach($db_hres4->result as $hres){
	    $hres_4sem=$hres_4sem+diff_heures($hres['debut'],$hres['fin'],"decimal");
	  }
	}
	$hres_4sem=" / <font title='Heures des 4 derni&egrave;res semaines'>$hres_4sem</font>";
      }

      //	Mise en forme de la ligne avec le nom et les heures et la couleur en fonction des heures faites
      $nom.="<span style='position:absolute; right:5px;'>\n";
      $nom.="&nbsp;<font title='Heures du jour'>$hres_jour</font> / ";
      $nom.="<font title='Heures de la semaine'>$hres_sem</font> / ";

      $nom.="<font title='$heuresHebdoTitle'>$heuresHebdo</font>";
      $nom.=$hres_4sem;
      $nom.="</span>\n";

      if($hres_jour>7)			// plus de 7h:jour : rouge
	$nom="<font style='color:red'>$nom</font>\n";
      elseif(($heuresHebdo-$hres_sem)<=0.5 and ($hres_sem-$heuresHebdo)<=0.5)		// 0,5 du quota hebdo : vert
	$nom="<font style='color:green'>$nom</font>\n";
      elseif($hres_sem>$heuresHebdo)			// plus du quota hebdo : rouge
	$nom="<font style='color:red'>$nom</font>\n";

      // Classe en fonction du statut et du service
      $class_tmp=array();
      if($elem['statut']){
	$class_tmp[]="statut_".strtolower(removeAccents(str_replace(" ","_",$elem['statut'])));
      }
      if($elem['service']){
	$class_tmp[]="service_".strtolower(removeAccents(str_replace(" ","_",$elem['service'])));
      }
      $classe=empty($class_tmp)?null:join(" ",$class_tmp);

      //	Affichage des lignes
      $menudiv.="<tr id='tr{$elem['id']}' style='height:21px;$display' onmouseover='$groupe_hide' class='$classe $classTrListe menudiv-tr'>\n";
      $menudiv.="<td style='width:200px;font-weight:normal;' onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",{$elem['id']},0,0,\"$site\");'>";
      $menudiv.=$nom;

      //	Afficher ici les horaires si besoin
      $menudiv.="</td><td style='text-align:right;width:20px'>";
      
      //	Affichage des liens d'ajout et de remplacement
      $max_perso=$nbAgents>=$GLOBALS['config']['Planning-NbAgentsCellule']?true:false;
      if($nbAgents>0 and !$max_perso and !$sr and !$sr_init)
	$menudiv.="<a href='javascript:bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",{$elem['id']},0,1,\"$site\");'>+</a>";
      if($nbAgents>0 and !$max_perso)
	$menudiv.="&nbsp;<a style='color:red' href='javascript:bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",{$elem['id']},1,1,\"$site\");'>x</a>&nbsp;";
      $menudiv.="</td></tr>\n";
    }
  $this->menudiv=$menudiv;

  }


  /**
  * @function notifications
  * @param string $this->date , date au format YYYY-MM-DD
  * Envoie des notifications en cas de validation ou changement de planning aux agents concernés
  */
  public function notifications(){
    $version="ajax";
    require_once "../../personnel/class.personnel.php";
    require_once "../../postes/class.postes.php";
    
    // Liste des agents actifs
    $p=new personnel();
    $p->fetch();
    $agents=$p->elements;

    // Listes des postes
    $p=new postes();
    $p->fetch();
    $postes=$p->elements;
    
    // Recherche des informations dans la table pl_poste pour la date $this->date
    $date=$this->date;
    $this->fetch();
    $tab=array();
    foreach($this->elements as $elem){
      // Si l'id concerne un agent qui a été supprimé, on l'ignore
      $id=$elem['perso_id'];
      if(!array_key_exists($id,$agents)){
	continue;
      }
      // Création d'un tableau par agent, avec nom, prénom et email
      if(!array_key_exists($id,$tab)){
	$tab[$id]=array("nom"=>$agents[$id]["nom"], "prenom"=>$agents[$id]["prenom"], "mail"=>$agents[$id]["mail"], "planning"=>array());
      }
      // Complète le tableau avec les postes, les sites, horaires et marquage "absent"
      $poste=$postes[$elem["poste"]]["nom"];
      $site=null;
      if($GLOBALS["config"]["Multisites-nombre"]>1){
	$site="(".$GLOBALS["config"]["Multisites-site{$elem["site"]}"].")";
      }
      $tab[$id]["planning"][]=array("debut"=> $elem["debut"], "fin"=> $elem["fin"], "absent"=> $elem["absent"], "site"=> $site, "poste"=> $poste);
    }
    
    // $perso_ids = agents qui recevront une notifications
    $perso_ids=array();

    // Recherche dans la table pl_notifications si des notifications ont déjà été envoyées (précédentes validations)
    $db=new db();
    $db->select2("pl_notifications","*",array("date"=>$date));
    
    // Si non, envoi d'un mail intitulé "planning validé" aux agents concernés par le planning
    // et enregistre les infos dans la table pl_notifications
    if(!$db->result){
      $notificationType="nouveauPlanning";

      // Enregistrement des infos dans la table BDD
      $insert=array("date"=>$date, "data"=>json_encode((array)$tab));
      $db=new db();
      $db->insert2("pl_notifications",$insert);

      // Enregistre les agents qui doivent être notifiés
      $perso_ids=array_keys($tab);
    }
    // Si oui, envoi d'un mail intitulé "planning modifié" aux agents concernés par une modification 
    // et met à jour les infos dans la table pl_notifications
    else{
      $notificationType="planningModifie";

      // Lecture des infos de la base de données, comparaison avec les nouvelles données
      // Lecture des infos de la base de données
      $db=new db();
      $db->select2("pl_notifications","*",array("date"=>$date));
      $data=str_replace("&quot;",'"',$db->result[0]["data"]);
      $data=(array) json_decode($data);
      $oldData=array();
      foreach($data as $key => $value){
	$oldData[$key]=(array) $value;
	foreach($oldData[$key]["planning"] as $k => $v){
	  $oldData[$key]["planning"][$k]=(array) $v;
	}
      }

      // Recherche des différences
      // Ajouts, modifications
      // Pour chaque agent présent dans le nouveau tableau
      foreach($tab as $key => $value){
	foreach($value["planning"] as $k => $v){
	  if(!array_key_exists($key, $oldData)
	    or (!array_key_exists($k, $oldData[$key]["planning"]))
	    or ($v != $oldData[$key]["planning"][$k])){
	    $perso_ids[]=$key;
	    continue 2;
	  }
	}
      }

      // Suppressions
      // Pour chaque agent présent dans l'ancien tableau
      foreach($oldData as $key => $value){
	foreach($value["planning"] as $k => $v){
	  if(!array_key_exists($key, $tab)
	    or (!array_key_exists($k, $tab[$key]["planning"]))
	    or ($v != $tab[$key]["planning"][$k])){
	      if(!in_array($key,$perso_ids)){
		$perso_ids[]=$key;
	      }
	    continue 2;
	  }
	}
      }

      // Modification des infos dans la BDD
      $update=array("data"=>json_encode((array)$tab));
      $db=new db();
      $db->update2("pl_notifications",$update,array("date"=>$date));
    }

    /*
    $tab[$perso_id] = Array(nom, prenom, mail, planning => Array(
	  [0] => Array(debut, fin, absent, site, poste), 
	  [1] => Array(debut, fin, absent, site, poste), ...))
    */

    // Envoi du mail
    $sujet=$notificationType=="nouveauPlanning"?"Validation du planning du ".dateFr($date):"Modification du planning du ".dateFr($date);
    
    // Tous les agents qui doivent être notifiés.
    foreach($perso_ids as $elem){
      // Création du message avec date et nom de l'agent
      $message=$notificationType=="nouveauPlanning"?"Validation du planning":"Modification du planning";
      $message.="<br/><br/>Date : <strong>".dateFr($date)."</strong>";
      $message.="<br/>Agent : <strong>{$tab[$elem]['prenom']} {$tab[$elem]['nom']}</strong>";
      
      // S'il y a des éléments, on ajoute la liste des postes occupés avec les horaires
      if(array_key_exists($elem,$tab)){
	$lines=array();
	$message.="<ul>";

	foreach($tab[$elem]["planning"] as $e){
	  // On marque en gras les modifications
	  $exists=true;
	  if($notificationType=="planningModifie"){
	    $exists=false;
	    foreach($oldData[$elem]["planning"] as $o){
	      if($e==$o){
		$exists=true;
		continue;
	      }
	    }
	  }
	  $bold=$exists?null:"font-weight:bold;";
	  $striped=$e['absent']?"text-decoration:line-through; color:red;":null;

	  // Affichage de la ligne avec horaires et poste
	  $line="<li><span style='$bold $striped'>".heure2($e['debut'])." - ".heure2($e['fin'])." : {$e['poste']} {$e['site']}";
	  if($GLOBALS['config']['Multisites-nombre']>1){
	    $line.=" ($site)";
	  }
	  $line.="</span>";

	  // On ajoute "(supprimé)" et une étoile en cas de modif car certains webmail suppriment les balises et le style "bold", etc.
	  if($striped){
	    $line.=" (supprim&eacute;)";
	  }
	  if($bold){
	    $line.="<sup style='font-weight:bold;'>*</sup>";
	  }
	  $line.="</li>";
	  $lines[]=array($e['debut'],$line);
	}

	// On affiche les suppressions
	foreach($oldData[$elem]["planning"] as $e){
	  $exists=false;
	  foreach($tab[$elem]["planning"] as $e2){
	    if($e['debut']==$e2['debut']){
	      $exists=true;
	      continue;
	    }
	  }
	  if(!$exists){
	    // Affichage de l'ancienne ligne avec horaires et poste
	    $line="<li><span style='font-weight:bold; text-decoration:line-through; color:red;'>".heure2($e['debut'])." - ".heure2($e['fin'])." : {$e['poste']} {$e['site']}";
	    if($GLOBALS['config']['Multisites-nombre']>1){
	      $line.=" ($site)";
	    }
	    $line.="</span>";
	    $line.=" (supprim&eacute;)";
	    $line.="<sup style='font-weight:bold;'>*</sup>";
	    $lines[]=array($e['debut'],$line);
	  }
	}

	sort($lines);
	foreach($lines as $line){
	  $message.=$line[1];
	}
	$message.="</ul>";

	// On ajoute le lien vers le planning
	$url=createURL("planning/poste/index.php&date=$date");
	$message.="Lien vers le planning du ".dateFr($date)." : $url";

	// Envoi du mail
	$m=new sendmail();
	$m->subject=$sujet;
	$m->message=$message;
	$m->to=$tab[$elem]['mail'];
	$m->send();

      // S'il n'y a pas d'éléments, on écrit "Vous n'êtes plus dans le planning ..."
      }else{
	// On ajoute le lien bers le planning
	$url=createURL("planning/poste/index.php&date=$date");
	$message.="Vous n&apos;&ecirc;tes plus dans le planning du ".dateFr($date);
	$message.="<br/><br/>Lien vers le planning du ".dateFr($date)." : $url";

	// Envoi du mail
	$m=new sendmail();
	$m->subject=$sujet;
	$m->message=$message;
	$m->to=$oldData[$elem]['mail'];
	$m->send();
      }
    }
  }
  
  // Notes
  // Récupère les notes (en bas des plannings)
  public function getNotes(){
    $this->notes=null;
    $db=new db();
    $db->select2("pl_notes","*",array("date"=>$this->date, "site"=>$this->site),"ORDER BY `timestamp` DESC");
    if($db->result){
      $notes=$db->result[0]['text'];
      $notes=str_replace(array("&lt;br/&gt;","#br#"),"<br/>",$notes);
      $this->notes=$notes;
      $this->notesTextarea=str_replace("<br/>","\n",$notes);
      if($db->result[0]['perso_id'] and $db->result[0]['timestamp']){
	$this->validation=nom($db->result[0]['perso_id']).", ".dateFr($db->result[0]['timestamp'],true);
      }else{
	$this->validation=null;
      }
    }
  }

  // Insertion, mise à jour des notes
  public function updateNotes(){
    $date=$this->date;
    $site=$this->site;
    $text=$this->notes;
    
    // Vérifie s'il y a eu des changements depuis le dernier enregistrement
    $this->getNotes();
    $previousNotes=str_replace("<br/>","#br#",$this->notes);
    // Si non, on enregistre la nouvelle note
    if(strcmp($previousNotes,$text)!=0){
      $db=new db();
      $db->insert2("pl_notes",array("date"=>$date,"site"=>$site,"text"=>$text,"perso_id"=>$_SESSION['login_id']));
    }
  }

  /**
  * Fonction sansRepas
  * Retourne true si l'agent est placé en continu entre les heures de début et de fin définies dans la config. pour les sans repas
  * @param string $date
  * @param string $debut
  * @param string $fin
  * @return int $sr (0/1)
  */
  public function sansRepas($date,$debut,$fin,$perso_id){
    if($GLOBALS['config']['Planning-sansRepas']==0){
      return 0;
    }

    $sr_debut=$GLOBALS['config']['Planning-SR-debut'];
    $sr_fin=$GLOBALS['config']['Planning-SR-fin'];
    // Par défaut, SR = false (pas de sans Repas)
    $sr=0;
    // Si la plage interrogée est dans ou à cheval sur la période de sans repas
    if($debut<$sr_fin and $fin>$sr_debut){
      // Recherche dans la base de données des autres plages concernées
      $db=new db();
      $db->select2("pl_poste","*",array("date"=>$date, "perso_id"=>$perso_id, "debut"=>"<$sr_fin", "fin"=>">$sr_debut"), "ORDER BY debut,fin");
      if($db->result){
	// Tableau result contient les plages de la base de données + la plage interrogée
	$result=array_merge($db->result,array(array("debut"=>$debut, "fin"=>$fin)));
	usort($result,"cmp_debut_fin");
	// Si le plus petit début et inférieur ou égal au début de la période SR et la plus grande fin supérieure ou égale à la fin de la période SR
	// = Possibilité que la période soit complète, on met SR=1
	if($result[0]["debut"]<=$sr_debut and $result[count($result)-1]["fin"]>=$sr_fin){
	  $sr=1;
	  // On consulte toutes les plages à la recherche d'une interruption. Si interruption, sr=0 et on quitte la boucle
	  $last_end=$result[0]['fin'];
	  for($i=1;$i<count($result);$i++){
	    if($result[$i]['debut']>$last_end){
	      $sr=0;
	      continue;
	    }
	    $last_end=$result[$i]['fin'];
	  }
	}
      }
    }
    return $sr;
  }

}
?>
