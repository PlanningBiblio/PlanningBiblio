<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/poste/class.planning.php
Création : 16 janvier 2013
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe planning 

Utilisée par les fichiers du dossier "planning/poste"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../../include/accessDenied.php";
}


class planning{
  public $CSRFToken = null;
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
  public function menudivAfficheAgents($poste,$agents,$date,$debut,$fin,$deja,$stat,$nbAgents,$sr_init,$hide,$deuxSP,$motifExclusion,$absences_non_validees){
    $msg_deja_place="&nbsp;<span class='red bold' title='L&apos;agent est d&eacute;j&agrave; plac&eacute; sur ce poste dans la journ&eacute;e'>(DP)</span>";
    $msg_deuxSP="&nbsp;<span class='red bold' title='2 plages de service public cons&eacute;cutives'>(2 SP)</span>";
    $msg_SR="&nbsp;<span class='red bold' title='Sans Repas, l&apos;agent n&apos; aucun cr&eacute;neau horaire pour prendre son repas'>(SR)</span>";
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
    $heuresSP=calculHeuresSP($date, $this->CSRFToken);

    // Nombre d'heures de la cellule choisie
    $hres_cellule = 0;
    if($stat){    // vérifier si le poste est compté dans les stats
      $hres_cellule = diff_heures($debut,$fin,"decimal");
    }
    
    // Calcul des heures d'absences afin d'ajuster les heures de SP
    $a=new absences();
    $a->CSRFToken = $this->CSRFToken;
    $heuresAbsencesTab=$a->calculHeuresAbsences($date);

    if(is_array($agents)){
      usort($agents,"cmp_nom_prenom");
    
      // Calcul des heures faites ce jour, cette seamine et sur les 4 dernières semaines pour tous les agents
      // Liste des ID des agents pour la requête des heures faites
      $ids = array();
      foreach($agents as $elem){
        $ids[]=$elem['id'];
      }
      $agents_liste = implode(",",$ids);
      
      // Intervalle de dates par défaut : la semaine en cours
      $date1 = $j1;
      $date2 = $j7;
      
      // Si l'option hres4semaines est cochée, l'intervalle est de 4 semaines
      if($config['hres4semaines']){
        $date1=date("Y-m-d",strtotime("-3 weeks",strtotime($j1)));
      }

      // Recherche des absences dans la table absences pour les déduire des heures faites
      $a=new absences();
      $a->valide=true;
      $a->fetch("`nom`,`prenom`,`debut`,`fin`",null,null,$date1." 00:00:00",$date2." 23:59:59");
      $absencesDB=$a->elements;

      // Recherche des postes occupés dans la base avec le plus grand intervalle pour limiter les requêtes
      $db_heures = new db();
      $db_heures->selectInnerJoin(array("pl_poste","poste"),array("postes","id"),
        array("date","debut","fin","perso_id"),
        array(),
        array("perso_id"=> "IN $agents_liste", "absent"=>"<>1", "date"=> "BETWEEN {$date1} AND {$date2}"),
        array("statistiques"=>"1"));
      
      if($db_heures->result){
        // Pour chaqe résultat, on ajoute le nombre d'heures correspondant à l'agent concerné, pour le jour, la semaine et/ou les 4 semaines
        foreach($db_heures->result as $elem){

          // Vérifie à partir de la table absences si l'agent est absent
          // S'il est absent, on met passe (continue 2)
          foreach($absencesDB as $a){
            if($elem['perso_id']==$a['perso_id'] and $a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']){
              continue 2;
            }
          }
        
          $h = diff_heures($elem['debut'],$elem['fin'],"decimal");
          $hres_jour = $elem['date'] == $date ? $h : 0;
          $hres_semaine = ($elem['date'] >= $j1 and $elem['date'] <= $j7) ? $h : 0;
          $hres_4sem = $h;
          
          if(!isset($heures[$elem['perso_id']])){
            $heures[$elem['perso_id']] = array("jour"=>$hres_jour, "semaine"=>$hres_semaine, "4semaines"=>$hres_4sem);
          }else{
            $heures[$elem['perso_id']]["jour"] += $hres_jour;
            $heures[$elem['perso_id']]["semaine"] += $hres_semaine;
            $heures[$elem['perso_id']]["4semaines"] += $hres_4sem;
          }
        }
      }
      
      // Recherche des sans repas en dehors de la boucle pour optimiser les performances (juillet 2016)
      $p = new planning();
      $sansRepas = $p->sansRepas($date,$debut,$fin);

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
              if(strpos($elem["heures_hebdo"],"%") and $elem["heures_hebdo"]!="100%"){
                $pourcent=" {$elem["heures_hebdo"]}";
              }
              
              $heuresHebdoTitle="Quota hebdomadaire = ".heure4($heuresHebdo,true)." - ".heure4($heuresAbsences,true)." (Absences{$pourcent})";
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
        
        if(is_numeric($heuresHebdo)){
          $heuresHebdo = round($heuresHebdo, 2);
        }

        if(!$config['ClasseParService']){
          if($elem['id']==2){		// on retire l'utilisateur "tout le monde"
            continue;
          }
        }
        
        $title = htmlentities($elem['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
        $title.= ' '.htmlentities($elem['prenom'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
        $title = in_array($elem['id'],$absences_non_validees) ? ' Absence ou congé non valid&eacute;' : $title;
        
        $nom = "<span class='menudiv-nom' title='$title'>";
        $nom .= htmlentities($elem['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
        if($elem['prenom']){
          $nom.=" ".substr(htmlentities($elem['prenom'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false),0,1).".";
        }
        $nom .='</span>';

        //			----------------------		Sans repas		------------------------------------------//
        // Si sans repas, on ajoute (SR) à l'affichage
        if( $sansRepas === true or in_array($elem['id'], $sansRepas) ){
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

        // affihage des heures faites ce jour et cette semaine + les heures de la cellule
        $hres_jour = isset($heures[$elem['id']]['jour']) ? $heures[$elem['id']]['jour'] : 0;
        $hres_jour += $hres_cellule;
        $hres_jour = round($hres_jour, 2);
        $hres_sem = isset($heures[$elem['id']]['semaine']) ? $heures[$elem['id']]['semaine'] : 0;
        $hres_sem += $hres_cellule;
        $hres_sem = round($hres_sem, 2);
        
        // affihage des heures faites les 4 dernières semaines + les heures de la cellule
        $hres_4sem=null;
        if($config['hres4semaines']){
          $hres_4sem = isset($heures[$elem['id']]['4semaines']) ? $heures[$elem['id']]['4semaines'] : 0;
          $hres_4sem += $hres_cellule;
          $hres_4sem = round($hres_4sem, 2);
          $hres_4sem=" / <font title='Heures des 4 derni&egrave;res semaines'>".heure4($hres_4sem,true)."</font>";
        }

        //	Mise en forme de la ligne avec le nom et les heures et la couleur en fonction des heures faites
        $nom.="<div class='menudiv-heures'>\n";
        $nom.="&nbsp;<font title='Heures du jour'>".heure4($hres_jour,true)."</font> / ";
        $nom.="<font title='Heures de la semaine'>".heure4($hres_sem,true)."</font> / ";

        $nom.="<font title='$heuresHebdoTitle'>".heure4($heuresHebdo,true)."</font>";
        $nom.=$hres_4sem;
        $nom.="</div>\n";

        // Si absence non validée : affichage en rouge
        if(in_array($elem['id'], $absences_non_validees)){
          $nom="<font style='color:red'>$nom</font>\n";
        }elseif($hres_jour>7)			// plus de 7h:jour : rouge
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
        $menudiv.="<td onclick='bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",{$elem['id']},0,0,\"$site\");'>";
        $menudiv.=$nom;

        //	Afficher ici les horaires si besoin
        $menudiv.="</td><td style='text-align:right;width:20px'>";
        
        //	Affichage des liens d'ajout et de remplacement
        $max_perso=$nbAgents>=$GLOBALS['config']['Planning-NbAgentsCellule']?true:false;
        if($nbAgents>0 and !$max_perso){
          $menudiv.="<a href='javascript:bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",{$elem['id']},0,1,\"$site\");'>+</a>";
          $menudiv.="&nbsp;<a style='color:red' href='javascript:bataille_navale(\"$poste\",\"$date\",\"$debut\",\"$fin\",{$elem['id']},1,1,\"$site\");'>x</a>&nbsp;";
        }
        $menudiv.="</td></tr>\n";
      }
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
      $poste = html_entity_decode($postes[$elem["poste"]]["nom"],ENT_QUOTES|ENT_IGNORE,'UTF-8');
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
      $db->CSRFToken = $this->CSRFToken;
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

      $data=$db->result[0]["data"];
      $data=html_entity_decode($data,ENT_QUOTES|ENT_IGNORE,'UTF-8');
      $data=json_decode($data, true);

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
      $db->CSRFToken = $this->CSRFToken;
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
      $agent = isset($tab[$elem]) ? $tab[$elem]['prenom'].' '.$tab[$elem]['nom'] : $oldData[$elem]['prenom'].' '.$oldData[$elem]['nom'];
      $message=$notificationType=="nouveauPlanning"?"Validation du planning":"Modification du planning";
      $message.="<br/><br/>Date : <strong>".dateFr($date)."</strong>";
      $message.="<br/>Agent : <strong>$agent</strong>";
      
      // S'il y a des éléments, on ajoute la liste des postes occupés avec les horaires
      if(array_key_exists($elem,$tab)){
	$lines=array();
	$message.="<ul>";

        if(isset($tab[$elem])){
          foreach($tab[$elem]["planning"] as $e){
            // On marque en gras les modifications
            $exists=true;
            if($notificationType=="planningModifie"){
              $exists=false;
              if(isset($oldData[$elem])){
                foreach($oldData[$elem]["planning"] as $o){
                  if($e==$o){
                    $exists=true;
                    continue;
                  }
                }
              }
            }
            $bold=$exists?null:"font-weight:bold;";
            $striped=$e['absent']?"text-decoration:line-through; color:red;":null;

            // Affichage de la ligne avec horaires et poste
            $line="<li><span style='$bold $striped'>".heure2($e['debut'])." - ".heure2($e['fin'])." : {$e['poste']} {$e['site']}";
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
	}

	// On affiche les suppressions
	if(isset($oldData[$elem])){
          foreach($oldData[$elem]["planning"] as $e){
            $exists=false;
            if(isset($tab[$elem])){
              foreach($tab[$elem]["planning"] as $e2){
                if($e['debut']==$e2['debut']){
                  $exists=true;
                  continue;
                }
              }
            }
            if(!$exists){
              // Affichage de l'ancienne ligne avec horaires et poste
              $line="<li><span style='font-weight:bold; text-decoration:line-through; color:red;'>".heure2($e['debut'])." - ".heure2($e['fin'])." : {$e['poste']} {$e['site']}";
              $line.="</span>";
              $line.=" (supprim&eacute;)";
              $line.="<sup style='font-weight:bold;'>*</sup>";
              $lines[]=array($e['debut'],$line);
            }
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
	$m=new CJMail();
	$m->subject=$sujet;
	$m->message=$message;
	$m->to=$tab[$elem]['mail'];
	$m->send();

      // S'il n'y a pas d'éléments, on écrit "Vous n'êtes plus dans le planning ..."
      }else{
	// On ajoute le lien bers le planning
	$url=createURL("planning/poste/index.php&date=$date");
	$message.="<br/><br/>Vous n&apos;&ecirc;tes plus dans le planning du ".dateFr($date);
	$message.="<br/><br/>Lien vers le planning du ".dateFr($date)." : $url";

	// Envoi du mail
	$m=new CJMail();
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
      $db->CSRFToken = $this->CSRFToken;
      $db->insert2("pl_notes",array("date"=>$date,"site"=>$site,"text"=>$text,"perso_id"=>$_SESSION['login_id']));
    }
  }

  /**
  * Fonction sansRepas
  * Retourne une tableau contenant les agents placés en continu entre les heures de début et de fin définies dans la config. pour les sans repas
  * Ou retourne true si la plage intérrogée couvre complétement la préiode définie dans la config.
  * @param string $date
  * @param string $debut
  * @param string $fin
  * @return array / true
  */
  public function sansRepas($date,$debut,$fin){
    if($GLOBALS['config']['Planning-sansRepas']==0){
      return array();
    }

    $sr_debut=$GLOBALS['config']['Planning-SR-debut'];
    $sr_fin=$GLOBALS['config']['Planning-SR-fin'];
    
    // Si la plage couvre complétement la période de sans repas, on retourne true, tous les agents seront marqués en sans repas
    if( $debut <= $sr_debut and $fin >= $sr_fin ){
      return true;
    }
    
    // Par défaut, personne en sans repas => $sr = tableau vide
    $sr=array();
    
    // Si la plage interrogée est dans ou à cheval sur la période de sans repas
    if($debut<$sr_fin and $fin>$sr_debut){

      // Recherche dans la base de données des autres plages concernées
      $db=new db();
      $db->select2("pl_poste","*",array("date"=>$date, "debut"=>"<$sr_fin", "fin"=>">$sr_debut"), "ORDER BY debut,fin");
      if($db->result){
        $result = array();
        // On classe les résultats par agent
        foreach($db->result as $elem){
          // On commence par ajouter la plage interrogée à chaque agent
          if(!array_key_exists($elem['perso_id'], $result)){
            $result[$elem['perso_id']] = array(array('debut'=>$debut, 'fin'=>$fin));
          }
          // Et on ajoute les plages déjà renseignées pour chaque agent
          $result[$elem['perso_id']][]=array('debut'=>$elem['debut'], 'fin'=>$elem['fin']);
        }
        
        // Tableau result contient pour chaque agent les plages de la base de données + la plage interrogée
        // Tri du tableau de chaque agents
        foreach($result as $key => $value){
          usort($result[$key],"cmp_debut_fin");
        }
        
        // Si le plus petit début et inférieur ou égal au début de la période SR et la plus grande fin supérieure ou égale à la fin de la période SR
        // = Possibilité que la période soit complète, on met SR=1
        foreach($result as $key => $value){
          $sansRepas=false;
          if($value[0]["debut"]<=$sr_debut and $value[count($value)-1]["fin"]>=$sr_fin){
            $sansRepas=true;
            // On consulte toutes les plages à la recherche d'une interruption. Si interruption, sr=0 et on quitte la boucle
            $last_end=$value[0]['fin'];
            for($i=1;$i<count($value);$i++){
              if($value[$i]['debut']>$last_end){
                $sansRepas=false;
                continue 2;
              }
              $last_end=$value[$i]['fin'];
            }
          }
          if($sansRepas){
            $sr[]=$key;
          }
        }
      }
    }
    return $sr;
  }
  
}
?>
