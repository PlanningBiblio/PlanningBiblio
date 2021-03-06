<?php
/**
Planning Biblio, Version 2.7.04
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/postes_renfort.php
Création : mai 2011
Dernière modification : 22 novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche les statistiques sur les postes de renfort : nombre d'heures d'ouverture, moyen par jour et par semaine, jours et
heures d'ouverture

Page appelée par le fichier index.php, accessible par le menu statistiques / Poste de renfort
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";
require_once "include/horaires.php";

use App\Model\AbsenceReason;

//	Variables :
$debut=filter_input(INPUT_POST, "debut", FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_POST, "fin", FILTER_SANITIZE_STRING);
$tri=filter_input(INPUT_POST, "tri", FILTER_SANITIZE_STRING);
$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_NUMBER_INT);

$debut=filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

$post_postes=isset($post['postes'])?$post['postes']:null;
$post_sites=isset($post['selectedSites'])?$post['selectedSites']:null;

$joursParSemaine=$config['Dimanche']?7:6;

//		--------------		Initialisation  des variables 'debut','fin' et 'poste'		-------------------
if (!$debut and array_key_exists('stat_debut', $_SESSION)) {
    $debut=$_SESSION['stat_debut'];
}
if (!$fin and array_key_exists('stat_fin', $_SESSION)) {
    $fin=$_SESSION['stat_fin'];
}
if (!$tri and array_key_exists('stat_poste_tri', $_SESSION)) {
    $tri=$_SESSION['stat_poste_tri'];
}

if (!$debut) {
    $debut="01/01/".date("Y");
}
if (!$fin) {
    $fin=date("d/m/Y");
}
if (!$tri) {
    $tri="cmp_01";
}

$_SESSION['stat_debut']=$debut;
$_SESSION['stat_fin']=$fin;
$_SESSION['stat_poste_tri']=$tri;

$debutSQL=dateFr($debut);
$finSQL=dateFr($fin);

// Postes
if (!array_key_exists('stat_postes_r', $_SESSION)) {
    $_SESSION['stat_postes_r']=null;
}

$postes=array();
if ($post_postes) {
    foreach ($post_postes as $elem) {
        $postes[]=$elem;
    }
} else {
    $postes=$_SESSION['stat_postes_r'];
}
$_SESSION['stat_postes_r']=$postes;

// Filtre les sites
if (!array_key_exists('stat_poste_sites', $_SESSION)) {
    $_SESSION['stat_poste_sites']=array();
}

$selectedSites=array();
if ($post_sites) {
    foreach ($post_sites as $elem) {
        $selectedSites[]=$elem;
    }
} else {
    $selectedSites=$_SESSION['stat_poste_sites'];
}

$_SESSION['stat_postes_r']=$postes;

// Filtre les sites
if (!array_key_exists('stat_poste_sites', $_SESSION)) {
    $_SESSION['stat_poste_sites']=null;
}

if ($config['Multisites-nombre']>1 and empty($selectedSites)) {
    for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
        $selectedSites[]=$i;
    }
}
$_SESSION['stat_poste_sites']=$selectedSites;

// Filtre les sites dans les requêtes SQL
if ($config['Multisites-nombre']>1 and is_array($selectedSites)) {
    $sitesSQL="0,".join(",", $selectedSites);
} else {
    $sitesSQL="0,1";
}

// Teleworking
$teleworking_absence_reasons = array();
$absences_reasons = $entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
foreach ($absences_reasons as $elem) {
    $teleworking_absence_reasons[] = $elem->valeur();
}

$tab=array();
$selected=null;

//		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
$db=new db();
$db->select2("postes", "*", array("obligatoire"=>"Renfort", "statistiques"=>"1"), "ORDER BY `etage`,`nom`");
$postes_list=$db->result;

if (!empty($postes)) {
    //	Recherche du nombre de jours concernés
    $db=new db();
    $debutREQ=$db->escapeString($debutSQL);
    $finREQ=$db->escapeString($finSQL);
    $sitesREQ=$db->escapeString($sitesSQL);
    $db->select("pl_poste", "`date`", "`date` BETWEEN '$debutREQ' AND '$finREQ' AND `site` IN ($sitesREQ)", "GROUP BY `date`;");
    $nbJours=$db->nb;

    // Recherche des absences dans la table absences
    $a=new absences();
    $a->valide=true;
    $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
    $absencesDB=$a->elements;

    //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
    //	On stock le tout dans le tableau $resultat
    $postes_select=join(',', $postes);

    $db=new db();
    $debutREQ=$db->escapeString($debutSQL);
    $finREQ=$db->escapeString($finSQL);
    $sitesREQ=$db->escapeString($sitesSQL);
    $postesREQ=$db->escapeString($postes_select);

    $req="SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
    `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
    `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
    `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
    FROM `{$dbprefix}pl_poste` 
    INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
    WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ' 
    AND `{$dbprefix}pl_poste`.`poste` IN ($postesREQ) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
    AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ) 
    ORDER BY `poste`,`date`,`debut`,`fin`;";
    $db->query($req);
    $resultat=$db->result;
  
    //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel)
    //	pour chaques postes sélectionnés
    foreach ($postes as $poste) {
        if (array_key_exists($poste, $tab)) {
            $heures=$tab[$poste][2];
            $sites=$tab[$poste]["sites"];
        } else {
            $heures=0;
            for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
                $sites[$i]=0;
            }
        }

        // $poste_tab : table of positions with id, name, area, mandatory/reinforcement, teleworking
        foreach ($postes_list as $elem) {
            if ($elem['id'] == $poste) {	
                $poste_tab = array($poste, $elem['nom'], $elem['etage'], $elem['obligatoire'], $elem['teleworking']);
                break;
            }
        }

        $agents=array();
        $dates=array();
        if (is_array($resultat)) {
            foreach ($resultat as $elem) {
                // Vérifie à partir de la table absences si l'agent est absent
                // S'il est absent : continue
                if ( !empty($absencesDB[$elem['perso_id']]) ) {
                    foreach ($absencesDB[$elem['perso_id']] as $a) {

                        // Ignore teleworking absences for compatible positions
                        if (in_array($a['motif'], $teleworking_absence_reasons) and $poste_tab[4]) {
                            continue;
                        }

                        if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                            continue 2;
                        }
                    }
                }

                if ($poste==$elem['poste']) {
                    // on créé un tableau par date
                    if (!array_key_exists($elem['date'], $dates)) {
                        $dates[$elem['date']]=array($elem['date'],array(),0,"site"=>$elem['site']);
                    }
                    $dates[$elem['date']][1][]=array($elem['debut'],$elem['fin'],diff_heures($elem['debut'], $elem['fin'], "decimal"));
                    $dates[$elem['date']][2]+=diff_heures($elem['debut'], $elem['fin'], "decimal");
                    // On compte les heures de chaque site
                    if ($config['Multisites-nombre']>1) {
                        $sites[$elem['site']]+=diff_heures($elem['debut'], $elem['fin'], "decimal");
                    }
                    // On compte toutes les heures (globales)
                    $heures+=diff_heures($elem['debut'], $elem['fin'], "decimal");

                    //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
                    $tab[$poste]=array($poste_tab,$dates,$heures,"sites"=>$sites);
                }
            }
        }
    }
}

// Heures et jours d'ouverture au public
$s=new statistiques();
$s->debut=$debutSQL;
$s->fin=$finSQL;
$s->joursParSemaine=$joursParSemaine;
$s->selectedSites=$selectedSites;
$s->ouverture();
$ouverture=$s->ouvertureTexte;

//		-------------		Tri du tableau		------------------------------
//	$tab[poste_id]=Array(Array(poste_id,poste_nom,etage,obligatoire),Array[perso_id]=Array(perso_id,nom,prenom,heures),heures)
usort($tab, $tri);

//	Passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
    
//		--------------		Affichage en 2 partie : formulaire à gauche, résultat à droite
echo "<h3>Statistiques par poste de renfort</h3>\n";
echo "<table><tr style='vertical-align:top;'><td id='stat-col1'>\n";
//		--------------		Affichage du formulaire permettant de sélectionner les dates et les postes		-------------
echo "<form name='form' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='statistiques/postes_renfort.php' />\n";
echo "<table>\n";
echo "<tr><td><label class='intitule'>Début</label></td>\n";
echo "<td><input type='text' name='debut' value='$debut' class='datepicker' />\n";
echo "</td></tr>\n";
echo "<tr><td><label class='intitule'>Fin</label></td>\n";
echo "<td><input type='text' name='fin' value='$fin' class='datepicker' />\n";
echo "</td></tr>\n";
echo "<tr><td><label class='intitule'>Tri</label></td>\n";
echo "<td>\n";
echo "<select name='tri' class='ui-widget-content ui-corner-all' >\n";
echo "<option value='cmp_01'>Nom du poste</option>\n";
echo "<option value='cmp_02'>Etage</option>\n";
echo "<option value='cmp_2'>Heures du - au +</option>\n";
echo "<option value='cmp_2desc'>Heures du + au -</option>\n";
echo "</select>\n";
echo "</td></tr>\n";
echo "<tr style='vertical-align:top'><td><label class='intitule'>Postes</label></td>\n";
echo "<td><select name='postes[]' multiple='multiple' size='20' onchange='verif_select(\"postes\");' class='ui-widget-content ui-corner-all' >\n";
if (is_array($postes_list)) {
    echo "<option value='Tous'>Tous</option>\n";
    foreach ($postes_list as $elem) {
        $selected = null;
        if (is_array($postes)) {
            $selected=in_array($elem['id'], $postes)?"selected='selected'":null;
        }
        echo "<option value='{$elem['id']}' $selected class='td_renfort'>{$elem['nom']} ({$elem['etage']})</option>\n";
    }
}
echo "</select></td></tr>\n";

if ($config['Multisites-nombre']>1) {
    $nbSites=$config['Multisites-nombre'];
    echo "<tr style='vertical-align:top'><td><label class='intitule'>Sites</label></td>\n";
    echo "<td><select name='selectedSites[]' multiple='multiple' size='".($nbSites+1)."' onchange='verif_select(\"selectedSites\");' class='ui-widget-content ui-corner-all' >\n";
    echo "<option value='Tous'>Tous</option>\n";
    for ($i=1;$i<=$nbSites;$i++) {
        $selected=in_array($i, $selectedSites)?"selected='selected'":null;
        echo "<option value='$i' $selected>{$config["Multisites-site$i"]}</option>\n";
    }
    echo "</select></td></tr>\n";
}

echo "<tr><td colspan='2' style='text-align:center;padding:10px;'>\n";
echo "<input type='button' value='Effacer' onclick='location.href=\"index.php?page=statistiques/postes_renfort.php&amp;debut=&amp;fin=&amp;postes=\"' class='ui-button' />\n";
echo "&nbsp;&nbsp;<input type='submit' value='OK' class='ui-button' />\n";
echo "</td></tr>\n";
echo "<tr><td colspan='2'><hr/></td></tr>\n";
echo "<tr><td>Exporter </td>\n";
echo "<td><a href='javascript:export_stat(\"postes_renfort\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"postes_renfort\",\"xsl\");'>XLS</a></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

//		--------------------------		2eme partie (colonne)		--------------------------
echo "</td><td>\n";

// 		--------------------------		Affichage du tableau de résultat		--------------------
if ($tab) {
    echo "<b>Statistiques par poste de renfort du $debut au $fin</b>\n";
    echo $ouverture;
    echo "<table border='1' cellspacing='0' cellpadding='0'>\n";
    echo "<tr class='th'>\n";
    echo "<td style='width:200px; padding-left:8px;'>Postes</td>\n";
    echo "<td style='width:300px; padding-left:8px;'>Horaires</td></tr>\n";
    foreach ($tab as $elem) {
        echo "<tr style='vertical-align:top;' class='td_renfort'><td>\n";
        //	Affichage du nom du poste dans la 1ère colonne
        // Sites
        $siteEtage=array();
        if ($config['Multisites-nombre']>1) {
            for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
                if ($elem["sites"][$i]==$elem[2]) {
                    $siteEtage[]=$config["Multisites-site{$i}"];
                    continue;
                }
            }
        }
        // Etages
        if ($elem[0][2]) {
            $siteEtage[]=$elem[0][2];
        }
        if (!empty($siteEtage)) {
            $siteEtage="(".join(" ", $siteEtage).")";
        } else {
            $siteEtage=null;
        }

        $jour=$elem[2]/$nbJours;
        $hebdo=$jour*$joursParSemaine;
        echo "<table><tr><td colspan='2'><b>{$elem[0][1]}</b></td></tr>";
        echo "<tr><td colspan='2'><i>$siteEtage</i></td></tr>\n";
        echo "<tr><td>Total</td>";
        echo "<td style='text-align:right;'>".heure4($elem[2])."</td></tr>\n";
        echo "<tr><td>Moyenne jour</td>";
        echo "<td style='text-align:right;'>".heure4(round($jour, 2))."</td></tr>\n";
        echo "<tr><td>Moyenne hebdo";
        echo "<td style='text-align:right;'>".heure4(round($hebdo, 2))."</td></tr>\n";
        if ($config['Multisites-nombre']>1) {
            for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
                if ($elem["sites"][$i] and $elem["sites"][$i]!=$elem[2]) {
                    // Calcul des moyennes
                    $jour=$elem["sites"][$i]/$nbJours;
                    $hebdo=$jour*$joursParSemaine;
                    echo "<tr><td colspan='2' style='padding-top:20px;'><u>".$config["Multisites-site{$i}"]."</u></td></tr>";
                    echo "<tr><td>Total</td>";
                    echo "<td style='text-align:right;'>".heure4($elem["sites"][$i])."</td></tr>";
                    ;
                    echo "<tr><td>Moyenne</td>";
                    echo "<td style='text-align:right;'>".heure4($hebdo)."</td></tr>";
                }
            }
        }
        echo "</table>\n";
        echo "</td>\n";
        //	Affichage des horaires d'ouverture
        echo "<td style='padding-left:8px;'>";
        foreach ($elem[1] as $date) {
            echo "<b>".dateAlpha($date[0])." : ".heure4($date[2])."</b><br/>";
            foreach ($date[1] as $horaires) {
                echo heure2($horaires[0])." - ".heure2($horaires[1])." : ".heure4($horaires[2])."<br/>\n";
            }
            echo "<br/>\n";
        }
        echo "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
}
//		----------------------			Fin d'affichage		----------------------------
echo "</td></tr></table>\n";
echo "<script type='text/JavaScript'>document.form.tri.value='$tri';</script>\n";
