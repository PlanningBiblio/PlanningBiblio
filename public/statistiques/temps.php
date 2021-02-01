<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/temps.php
Création : mai 2011
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche un tableau avec le nombre d'heures de service public effectué par agent par jour et par semaine

Page appelée par le fichier index.php, accessible par le menu statistiques / Feuille de temps
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";
require_once "postes/class.postes.php";

echo "<h3>Feuille de temps</h3>\n";

require_once "include/horaires.php";

//	Initialisation des variables
$CSRFToken=trim(filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING));
if (!$CSRFToken) {
    $CSRFToken = $CSRFSession;
}

$debut=filter_input(INPUT_GET, "debut", FILTER_SANITIZE_STRING);
if ($debut) {
    $fin=filter_input(INPUT_GET, "fin", FILTER_SANITIZE_STRING);
    $selection_groupe = filter_input(INPUT_GET, 'selection_groupe', FILTER_SANITIZE_STRING);

    $debut=filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
    $fin=filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
    $selection_groupe=filter_var($selection_groupe, FILTER_CALLBACK, array("options"=>"sanitize_on"));

    $debut=dateSQL($debut);
    $fin=$fin?dateFr($fin):$debut;
} elseif (array_key_exists("stat_temps_debut", $_SESSION['oups'])) {
    $debut=$_SESSION['oups']['stat_temps_debut'];
    $fin=$_SESSION['oups']['stat_temps_fin'];
    $selection_groupe=$_SESSION['oups']['stat_temps_selection_groupe'];
} else {
    $date=$_SESSION['PLdate'];
    $d=new datePl($date);
    $debut=$d->dates[0];
    $fin=$config['Dimanche']?$d->dates[6]:$d->dates[5];
    $selection_groupe = false;
}
$_SESSION['oups']['stat_temps_debut']=$debut;
$_SESSION['oups']['stat_temps_fin']=$fin;
$_SESSION['oups']['stat_temps_selection_groupe']=$selection_groupe;


$current=$debut;
while ($current<=$fin) {
    if (date("w", strtotime($current))==0 and !$config['Dimanche']) {
    } else {
        $dates[]=array($current,dateAlpha2($current));
    }
    $current=date("Y-m-d", strtotime("+1 day", strtotime($current)));
}

$debutFr=dateFr($debut);
$finFr=dateFr($fin);
$heures=array(); 	// Nombre total d'heures pour chaque jour
$agents=array();	// Même chose avec le nombre d'agents
$agents_id=array();	// Utilisé pour compter les agents présents chaque jour
$nbAgents=array();	// Nombre d'agents pour chaque jour
$tab=array();
$nb=count($dates);	// Nombre de dates
$nbSemaines=$nb/($config['Dimanche']?7:6);	// Nombre de semaines
$totalAgents=0;		// Les totaux
$totalHeures=0;
$siteHeures=array(0,0);	// Heures par site
$siteAgents=array(0,0);	// Agents par site

// Affichage des statistiques par groupe de postes
$groupes = array();
$groupes_keys = array();
$affichage_groupe = null;
$totauxGroupesHeures = null;
$totauxGroupesPerso = null;

$p = new postes();
$p->fetch();
// Rassemble les postes dans un tableau en fonction de leur groupe (ex: $groupe['pret'] = array(1,2,3))
foreach ($p->elements as $poste) {
    $groupes[$poste['groupe']][]=$poste['id'];
}

if (!empty($groupes) and count($groupes)>1) {
    $checked = $selection_groupe ? "checked='checked'" : null;
    $affichage_groupe = "<span id='stat-temps-aff-grp'><input type='checkbox' value='on' id='selection_groupe' name='selection_groupe' $checked /><label for='selection_groupe'>Afficher les heures par groupe de postes</label></span>";
}

if ($affichage_groupe and $selection_groupe) {
    // $groupes_keys : nom des groupes
    $keys = array_keys($groupes);

    // Affichage des groupes selon l'ordre du menu déroulant
    $db=new db();
    $db->select2('select_groupes', 'valeur', null, 'order by rang');
    if ($db->result) {
        foreach ($db->result as $elem) {
            if (in_array($elem['valeur'], $keys)) {
                $groupes_keys[]=$elem['valeur'];
            }
        }
    }
    // Autres (les postes qui ne sont pas affectés à des groupes)
    if (in_array('', $keys)) {
        $groupes_keys[]='';
    }


    // Initialisation des totaux (footer)
    foreach ($groupes_keys as $g) {
        $totauxGroupesHeures[$g] = 0;
        $totauxGroupesPerso[$g] = array();
    }
}

// Recherche des heures de SP à effectuer pour tous les agents pour toutes les semaines demandées
$d=new datePl($debut);
$d1=$d->dates[0];
// Pour chaque semaine
for ($d=$d1;$d<=$fin;$d=date("Y-m-d", strtotime($d."+1 week"))) {
    $heuresSP[$d]=calculHeuresSP($d, $CSRFToken);
    // déduction des absences
    if ($config['Planning-Absences-Heures-Hebdo']) {
        $a=new absences();
        $a->CSRFToken = $CSRFToken;
        $heuresAbsences[$d]=$a->calculHeuresAbsences($d);
        foreach ($heuresAbsences[$d] as $key => $value) {
            if (array_key_exists($key, $heuresSP[$d])) {
                $heuresSP[$d][$key]=$heuresSP[$d][$key]-$value;
                if ($heuresSP[$d][$key]<0) {
                    $heuresSP[$d][$key]=0;
                }
            }
        }
    }
}
// Calcul des totaux d'heures de SP à effectuer sur la période
$totalSP=array();
foreach ($heuresSP as $key => $value) {		// $key=date, $value=array
  foreach ($value as $key2 => $value2) {		// $key2=perso_id, $value2=heures
    if (!array_key_exists($key2, $totalSP)) {
        $totalSP[$key2]=(float) $value2;
    } else {
        $totalSP[$key2]+=(float) $value2;
    }
  }
}
// Calcul des moyennes hebdomadaires de SP à effectuer
$moyennesSP=array();
foreach ($totalSP as $key => $value) {
    $moyennesSP[$key]=$value/(count($heuresSP));
}


// Recherche des absences dans la table absences
$a=new absences();
$a->valide=true;
$a->agents_supprimes = array(0,1,2);
$a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $debut." 00:00:00", $fin." 23:59:59");
$absencesDB=$a->elements;

$db=new db();
$debutREQ=$db->escapeString($debut);
$finREQ=$db->escapeString($fin);

$req="SELECT `{$dbprefix}pl_poste`.`date` AS `date`, `{$dbprefix}pl_poste`.`debut` AS `debut`, ";
$req.="`{$dbprefix}pl_poste`.`fin` AS `fin`, `{$dbprefix}personnel`.`id` AS `perso_id`, ";
$req.="`{$dbprefix}pl_poste`.`site` AS `site`, `{$dbprefix}pl_poste`.`poste` AS `poste`, ";
$req.="`{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, ";
$req.="`{$dbprefix}personnel`.`statut` AS `statut` ";
$req.="FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` ";
$req.="INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}postes`.`id`=`{$dbprefix}pl_poste`.`poste` ";
$req.="WHERE `date`>='$debutREQ' AND `date`<='$finREQ' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1' ";
$req.="ORDER BY `nom`,`prenom`;";

$db->query($req);
if ($db->result) {
    foreach ($db->result as $elem) {

    // Vérifie à partir de la table absences si l'agent est absent
        // S'il est absent, on met à 1 la variable $elem['absent']
        foreach ($absencesDB as $a) {
            if ($elem['perso_id']==$a['perso_id'] and $a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                continue 2;
            }
        }

        if (!array_key_exists($elem['perso_id'], $tab)) {		// création d'un tableau de données par agent (id, nom, heures de chaque jour ...)
            $tab[$elem['perso_id']] = array("perso_id"=>$elem['perso_id'],"nom"=>$elem['nom'],
      "prenom"=>$elem['prenom'],"statut"=>$elem['statut'],"site1"=>0,"site2"=>0,"total"=>0,
      "semaine"=>0);
            foreach ($dates as $d) {
                $tab[$elem['perso_id']][$d[0]] = array('total'=>0);
                if (!empty($groupes_keys)) {
                    foreach ($groupes_keys as $g) {
                        $tab[$elem['perso_id']][$d[0]]["group_$g"] = 0;
                    }
                }
            }

            // Totaux par groupe de postes
            foreach ($groupes_keys as $g) {
                $tab[$elem['perso_id']]['group_'.$g] = 0;
            }
        }

        $d=new datePl($elem['date']);
        $position=$d->position!=0?$d->position-1:6;
        $tab[$elem['perso_id']][$elem['date']]['total']+=diff_heures($elem['debut'], $elem['fin'], "decimal");	// ajout des heures par jour
    $tab[$elem['perso_id']]['total']+=diff_heures($elem['debut'], $elem['fin'], "decimal");	// ajout des heures sur toutes la période
    if ($elem["site"]) {
        if (!array_key_exists("site{$elem['site']}", $tab[$elem['perso_id']])) {
            $tab[$elem['perso_id']]["site{$elem['site']}"]=0;
        }
        $tab[$elem['perso_id']]["site{$elem['site']}"]+=diff_heures($elem['debut'], $elem['fin'], "decimal");	// ajout des heures sur toutes la période par site
    }
        $totalHeures+=diff_heures($elem['debut'], $elem['fin'], "decimal");		// compte la somme des heures sur la période
        if (!array_key_exists($elem['site'], $siteHeures)) {
            $siteHeures[$elem['site']]=0;
        }
        $siteHeures[$elem['site']]+=diff_heures($elem['debut'], $elem['fin'], "decimal");

        // Totaux par groupe de postes
        foreach ($groupes_keys as $g) {
            if (in_array($elem['poste'], $groupes[$g])) {
                $tab[$elem['perso_id']]['group_'.$g] +=diff_heures($elem['debut'], $elem['fin'], "decimal");
                $tab[$elem['perso_id']][$elem['date']]['group_'.$g] +=diff_heures($elem['debut'], $elem['fin'], "decimal");
                $totauxGroupesHeures[$g] +=diff_heures($elem['debut'], $elem['fin'], "decimal");
                if (!in_array($elem['perso_id'], $totauxGroupesPerso[$g])) {
                    $totauxGroupesPerso[$g][] = $elem['perso_id'];
                }
            }
        }
    }
}

// Totaux par groupe de postes
foreach ($groupes_keys as $g) {
    $totauxGroupesPerso[$g] = count($totauxGroupesPerso[$g]);
}

// pour chaque jour, on compte les heures et les agents
foreach ($dates as $d) {
    $agents_id=array();
    if (is_array($tab)) {
        foreach ($tab as $elem) {
            // on compte les heures de chaque agent
            if (!array_key_exists($d[0], $agents)) {
                $agents[$d[0]]=0;
            }
            if (array_key_exists($d[0], $elem)) {
                $agents[$d[0]]++;
            }
            // on compte le total d'heures par jours
            if (!array_key_exists($d[0], $heures)) {
                $heures[$d[0]]=0;
            }
            if (array_key_exists($d[0], $elem)) {
                $heures[$d[0]]+=$elem[$d[0]]['total'];
            }
            // on compte les agents par jours	+ le total sur la période
            if (!in_array($elem['perso_id'], $agents_id) and $elem[$d[0]]['total']) {
                $agents_id[]=$elem['perso_id'];
                $totalAgents++;

                for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
                    if (array_key_exists("site$i", $elem)) {
                        if (!array_key_exists($i, $siteAgents)) {
                            $siteAgents[$i]=0;
                        }
                        $siteAgents[$i]++;
                    }
                }
            }
        }
    }
    // on compte les agents par jours (2ème partie)
    $nbAgents[$d[0]]=count($agents_id);
}

// Formatage des données pour affichage
$keys=array_keys($tab);
foreach ($keys as $key) {
    for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
        $tab[$key]["site{$i}Semaine"]=array_key_exists("site{$i}", $tab[$key])?number_format($tab[$key]["site{$i}"]/$nbSemaines, 2, '.', ' '):"-";
        $tab[$key]["site{$i}"]=array_key_exists("site{$i}", $tab[$key])?number_format($tab[$key]["site{$i}"], 2, '.', ' '):"-";
    }
    $tab[$key]['total']=number_format($tab[$key]['total'], 2, '.', ' ');
    $tab[$key]['semaine']=number_format($tab[$key]['total']/$nbSemaines, 2, '.', ' ');		// ajout la moyenne par semaine

    if (!array_key_exists($key, $moyennesSP) or !is_numeric($moyennesSP[$key])) {
        $tab[$key]['heuresHebdo']="Erreur";
    } elseif ($moyennesSP[$key]>0) {
        $tab[$key]['heuresHebdo']=number_format($moyennesSP[$key], 2, '.', ' ');
    } else {
        $tab[$key]['heuresHebdo']=0;
    }

    if (!array_key_exists($key, $totalSP) or !is_numeric($totalSP[$key])) {
        $tab[$key]['max']="Erreur";
    } elseif ($totalSP[$key]>0) {
        $tab[$key]['max']=number_format($totalSP[$key], 2, '.', ' ');
    } else {
        $tab[$key]['max']=0;
    }

    foreach ($dates as $d) {
        $tab[$key][$d[0]]['total'] = $tab[$key][$d[0]]['total'] != 0 ? number_format($tab[$key][$d[0]]['total'], 2, '.', ' ') : '-';
    }
}

foreach ($dates as $d) {
    if (array_key_exists($d[0], $heures)) {
        $heures[$d[0]]=$heures[$d[0]]!=0?number_format($heures[$d[0]], 2, '.', ' '):"-";
    } else {
        $heures[$d[0]]="-";
    }
    if (array_key_exists($d[0], $nbAgents)) {
        $nbAgents[$d[0]]=$nbAgents[$d[0]]!=0?$nbAgents[$d[0]]:"-";
    } else {
        $nbAgents[$d[0]]="-";
    }
}
$totalHeures=$totalHeures!=0?number_format($totalHeures, 2, '.', ' '):"-";

for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
    if (array_key_exists($i, $siteHeures) and $siteHeures[$i]!=0) {
        $siteHeures[$i]=number_format($siteHeures[$i], 2, '.', ' ');
    } else {
        $siteHeures[$i]="-";
    }
    if (array_key_exists($i, $siteAgents) and $siteAgents[$i]!=0) {
        $siteAgents[$i]=$siteAgents[$i];
    } else {
        $siteAgents[$i]="-";
    }
}


// passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
$_SESSION['stat_heures']=$heures;
$_SESSION['stat_agents']=$agents;
$_SESSION['stat_dates']=$dates;
$_SESSION['oups']['stat_totalHeures']=$totalHeures;
$_SESSION['oups']['stat_nbAgents']=$nbAgents;
$_SESSION['oups']['stat_groupes'] = $groupes_keys;
$_SESSION['oups']['stat_groupesHeures'] = $totauxGroupesHeures;
$_SESSION['oups']['stat_groupesPerso'] = $totauxGroupesPerso;


//			-------------		Affichage du tableau		---------------------//
echo <<<EOD
<table>
<tr><td style='width:350px;'><b>Du $debutFr au $finFr</b></td>
<td>
<form name='form' method='get' action='index.php'>
<input type='hidden' name='page' value='statistiques/temps.php' />
<input type='hidden' name='CSRFToken' value='$CSRFToken' />
<label for='debut'>Début</label><input type='text' name='debut' class='datepicker' value='$debutFr' style='margin:0 20px 0 5px;' />
<label for='fin'>Fin</label><input type='text' name='fin' class='datepicker' value='$finFr' style='margin:0 0 0 5px;' />
$affichage_groupe
<input type='submit' value='OK' id='submit' class='ui-button' style='margin-left:30px;'/></form>
</td></tr></table>
<br/>
EOD;

// S'il y a des éléments, affiche le tableau
if (is_array($tab)) {
    echo <<<EOD
  <table id='tableStatTemps' class='CJDataTable' data-fixedColumns='2'>
  <thead>
  <tr>
  <th>Agents</th>
  <th>Statut</th>
EOD;
    foreach ($dates as $d) {
        echo "<th class='dataTableHeureFR'>{$d[1]}</th>\n";
    }

    // Total par site
    if ($config['Multisites-nombre']>1) {
        for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
            echo "<th class='dataTableHeureFR'>".$config["Multisites-site$i"]."</th>\n";
            if ($nbSemaines!=1) {
                echo "<th class='dataTableHeureFR'>Moyenne Hebdo.</th>\n";
            }
        }
    }

    // Totaux par groupe de postes
    if (!empty($groupes_keys)) {
        foreach ($groupes_keys as $g) {
            if ($g != '' and $totauxGroupesPerso[$g]) {
                echo "<th class='dataTableHeureFR'>$g</th>\n";
            }
        }
        if (in_array('', $groupes_keys) and $totauxGroupesPerso['']) {
            echo "<th class='dataTableHeureFR'>Autres</th>\n";
        }
    }

    // Total, moyenne, max
    echo "<th class='dataTableHeureFR'>Total</th>\n";
    echo "<th class='dataTableHeureFR'>Max.</th>\n";

    //Si nbSemaine == 1, le total=moyenne : on ne l'affiche pas
    $colspan=1;
    if ($nbSemaines!=1) {
        $colspan=3;
        echo "<th class='dataTableHeureFR'>Moyenne<br/>Hebdo.</th>\n";
        echo "<th class='dataTableHeureFR'>Max. Hebdo.</th>\n";
    }

    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";

    foreach ($tab as $elem) {

    // Couleurs en fonction de la moyenne hebdo et des heures prévues
        $color=$elem['semaine']>$elem['heuresHebdo']?"background:#FF5E5E; font-weight:bold;":null;
        if (($elem['heuresHebdo']-$elem['semaine'])<=0.5 and ($elem['semaine']-$elem['heuresHebdo'])<=0.5) {		// 0,5 du quota hebdo : vert
            $color="background:lightgreen; font-weight:bold;";
        }

        // Affichage des lignes : Nom, heures par jour, par semaine, heures prévues
        echo "<tr style='vertical-align:top;'><td>{$elem['nom']} {$elem['prenom']}</td>\n";
        $elem['statut']=$elem['statut']?$elem['statut']:"&nbsp;";
        echo "<td>{$elem['statut']}</td>\n";
        foreach ($dates as $d) {
            $class=$elem[$d[0]]['total']!="-"?"bg-yellow":null;
            echo "<td class='$class' style='text-align:center;'>\n";
            echo "<strong>".heure4($elem[$d[0]]['total'])."</strong>\n";
            if (!empty($groupes_keys)) {
                echo "<br/>";
                foreach ($groupes_keys as $g) {
                    if ($elem[$d[0]]["group_$g"]) {
                        echo "<br/>";
                        echo $g ? $g : 'Autres';
                        echo " : ";
                        echo heure4($elem[$d[0]]["group_$g"]);
                    }
                }
            }

            echo "</td>\n";
        }

        if ($config['Multisites-nombre']>1) {
            for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
                echo "<td style='text-align:center;'>".heure4($elem["site$i"])."</td>\n";
                if ($nbSemaines!=1) {
                    echo "<td style='text-align:center;'>".heure4($elem["site{$i}Semaine"])."</td>\n";
                }
            }
        }

        // Totaux par groupe de postes
        if (!empty($groupes_keys)) {
            foreach ($groupes_keys as $g) {
                if ($g != '' and $totauxGroupesPerso[$g]) {
                    $h = $elem["group_$g"] ? heure4($elem["group_$g"]) : '-';
                    echo "<td style='text-align:center;'>$h</td>\n";
                }
            }
            if (in_array('', $groupes_keys) and $totauxGroupesPerso['']) {
                $h = $elem["group_"] ? heure4($elem["group_"]) : '-';
                echo "<td style='text-align:center;'>$h</td>\n";
            }
        }

        if ($nbSemaines!=1) {
            echo "<td style='text-align:center; $color'>".heure4($elem['total'])."</td>\n";
            echo "<td style='text-align:center;'>".heure4($elem['max'], true)."</td>\n";
        }
        echo "<td style='text-align:center; $color'>".heure4($elem['semaine'])."</td>\n";
        echo "<td style='text-align:center;'>".heure4($elem['heuresHebdo'], true)."</td>\n";
        echo "</tr>\n";
    }
    echo "</tbody>\n";

    // Affichage de la ligne "Nombre d'heures"
    echo "<tfoot><tr style='background:#DDDDDD;'><th colspan='2'>Nombre d'heures</th>\n";

    foreach ($dates as $d) {
        echo "<th>".heure4($heures[$d[0]])."</th>\n";
    }

    if ($config['Multisites-nombre']>1) {
        for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
            echo "<th>".heure4($siteHeures[$i])."</th>\n";
            if ($nbSemaines!=1) {
                echo "<th>&nbsp;</th>\n";
            }
        }
    }

    // Totaux par groupe de postes
    if (!empty($groupes_keys)) {
        foreach ($groupes_keys as $g) {
            if ($g != '' and $totauxGroupesPerso[$g]) {
                echo "<th>".heure4($totauxGroupesHeures[$g])."</th>\n";
            }
        }
        if (in_array('', $groupes_keys) and $totauxGroupesPerso['']) {
            echo "<th>".heure4($totauxGroupesHeures[''])."</th>\n";
        }
    }

    echo "<th>".heure4($totalHeures)."</th><th colspan='$colspan'>&nbsp;</th>\n";
    echo "</tr>\n";


    // Affichage de la ligne "Nombre d'agents"
    echo "<tr style='background:#DDDDDD;'><th colspan='2'>Nombre d'agents</th>\n";
    foreach ($dates as $d) {
        echo "<th>{$nbAgents[$d[0]]}</th>\n";
    }

    if ($config['Multisites-nombre']>1) {
        for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
            echo "<th>{$siteAgents[$i]}</th>\n";
            if ($nbSemaines!=1) {
                echo "<th>&nbsp;</th>\n";
            }
        }
    }

    // Totaux par groupe de postes
    if (!empty($groupes_keys)) {
        foreach ($groupes_keys as $g) {
            if ($g != '' and $totauxGroupesPerso[$g]) {
                echo "<th>{$totauxGroupesPerso[$g]}</th>\n";
            }
        }
        if (in_array('', $groupes_keys) and $totauxGroupesPerso['']) {
            echo "<th>{$totauxGroupesPerso['']}</th>\n";
        }
    }

    echo "<th>$totalAgents</th><th colspan='$colspan'>&nbsp;</th>\n";
    echo "</tr>\n";

    echo "</tfoot>\n";
    echo "</table>\n";
    echo "<br/>Exporter \n";
    echo "<a href='javascript:export_stat(\"temps\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
    echo "<a href='javascript:export_stat(\"temps\",\"xls\");'>XLS</a>\n";
} else {			// Si pas d'élément
    echo "Les plannings de la période choisie sont vides.<br/><br/><br/><br/><br/><br/>";
}
