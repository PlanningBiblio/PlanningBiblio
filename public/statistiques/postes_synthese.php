<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/postes_synthese.php
Création : mai 2011
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche le nombre total d'heures d'ouverture de chaque poste, la moyen par jour et par semaine

Page appelée par le fichier index.php, accessible par le menu statistiques / Par poste (Synthèse)
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";
require_once "include/horaires.php";

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
if (!array_key_exists('stat_poste_postes', $_SESSION)) {
    $_SESSION['stat_poste_postes']=null;
}

$postes=array();
if ($post_postes) {
    foreach ($post_postes as $elem) {
        $postes[]=$elem;
    }
} else {
    $postes=$_SESSION['stat_poste_postes'];
}
$_SESSION['stat_poste_postes']=$postes;

// Filtre les sites
if (!array_key_exists('stat_poste_sites', $_SESSION)) {
    $_SESSION['stat_poste_sites']=array();
}

if ($post_sites) {
    $selectedSites=array();
    foreach ($post_sites as $elem) {
        $selectedSites[]=$elem;
    }
} else {
    $selectedSites=$_SESSION['stat_poste_sites'];
}

$_SESSION['stat_poste_postes']=$postes;

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

// Sélection des étages
$etagesTab = array();
$db = new db();
$db->select("select_etages");
if ($db->result) {
    foreach ($db->result as $elem) {
        $etagesTab[$elem["id"]] = $elem["valeur"];
    }
}

$tab=array();

$total_heures=0;
$total_jour=0;
$total_hebdo=0;
$selected=null;

//		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}postes` WHERE `statistiques`='1' ORDER BY `etage`,`nom`;");
$postes_list=$db->result;

if (!empty($postes)) {
    //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
    //	On stock le tout dans le tableau $resultat
    $postes_select=join($postes, ",");
    $db=new db();
    $debutREQ=$db->escapeString($debutSQL);
    $finREQ=$db->escapeString($finSQL);
    $sitesREQ=$db->escapeString($sitesSQL);
    $postesREQ=$db->escapeString($postes_select);

    // Recherche des absences dans la table absences
    $a=new absences();
    $a->valide=true;
    $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
    $absencesDB=$a->elements;

    $req="SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
    `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
    `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
    `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
    FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` 
    ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
    WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ' 
    AND `{$dbprefix}pl_poste`.`poste` IN ($postesREQ) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
    AND `{$dbprefix}pl_poste`.`supprime`<>'1'  AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ) 
    ORDER BY `poste`,`nom`,`prenom`;";
    $db->query($req);
    $resultat=$db->result;
  
    //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel) pour chaque poste sélectionné
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
        $agents=array();
        if (is_array($resultat)) {
            foreach ($resultat as $elem) {
                if ($poste==$elem['poste']) {
                    // Vérifie à partir de la table absences si l'agent est absent
                    // S'il est absent : continue
                    if ( !empty($absencesDB[$elem['perso_id']]) ) {
                        foreach ($absencesDB[$elem['perso_id']] as $a) {
                            if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                continue 2;
                            }
                        }
                    }
                    //	On créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
                    if (!array_key_exists($elem['perso_id'], $agents)) {
                        $agents[$elem['perso_id']]=array($elem['perso_id'],$elem['nom'],$elem['prenom'],0,"site"=>$elem['site']);
                    }
                    $agents[$elem['perso_id']][3]+=diff_heures($elem['debut'], $elem['fin'], "decimal");
                    // On compte les heures de chaque site
                    if ($config['Multisites-nombre']>1) {
                        $sites[$elem['site']]+=diff_heures($elem['debut'], $elem['fin'], "decimal");
                    }
                    // On compte toutes les heures (globales)
                    $heures+=diff_heures($elem['debut'], $elem['fin'], "decimal");
      
                    foreach ($postes_list as $elem2) {
                        if ($elem2['id']==$poste) {	// on créé un tableau avec le nom et l'étage du poste.
                            $etage = $elem2['etage'] > 0 ? $etagesTab[$elem2['etage']] : null;
                            $poste_tab=array($poste,$elem2['nom'],$etage,$elem2['obligatoire']);
                            break;
                        }
                    }
                    //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
                    $tab[$poste]=array($poste_tab,$agents,$heures,"sites"=>$sites);
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
usort($tab, $tri);

// passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
    
//		--------------		Affichage en 2 partie : formulaire à gauche, résultat à droite
echo "<h3>Statistiques par poste (Synthèse)</h3>\n";
echo "<table><tr style='vertical-align:top;'><td id='stat-col1'>\n";
//		--------------		Affichage du formulaire permettant de sélectionner les dates et les postes		-------------
echo "<form name='form' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='statistiques/postes_synthese.php' />\n";
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
echo "<option value='cmp_03'>Obligatoire</option>\n";
echo "<option value='cmp_03desc'>Renfort</option>\n";
echo "<option value='cmp_2'>Heures du - au +</option>\n";
echo "<option value='cmp_2desc'>Heures du + au -</option>\n";
echo "</select>\n";
echo "</td></tr>\n";
echo "<tr style='vertical-align:top'><td><label class='intitule'>Postes</label></td>\n";
echo "<td><select name='postes[]' multiple='multiple' size='20' onchange='verif_select(\"postes\");'class='ui-widget-content ui-corner-all' >\n";
if (is_array($postes_list)) {
    echo "<option value='Tous'>Tous</option>\n";
    foreach ($postes_list as $elem) {
        if ($postes) {
            $selected=in_array($elem['id'], $postes)?"selected='selected'":null;
        }
        $class=$elem['obligatoire']=="Obligatoire"?"td_obligatoire":"td_renfort";
        $etage = $elem['etage']?"({$etagesTab[$elem['etage']]})":null;
        echo "<option value='{$elem['id']}' $selected class='$class'>{$elem['nom']} $etage</option>\n";
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
echo "<input type='button' value='Effacer' onclick='location.href=\"index.php?page=statistiques/postes_synthese.php&amp;debut=&amp;fin=&amp;postes=\"' class='ui-button' />\n";
echo "&nbsp;&nbsp;<input type='submit' value='OK' class='ui-button' />\n";
echo "</td></tr>\n";
echo "<tr><td colspan='2'><hr/></td></tr>\n";
echo "<tr><td>Exporter </td>\n";
echo "<td><a href='javascript:export_stat(\"postes_synthese\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"postes_synthese\",\"xsl\");'>XLS</a></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

//		--------------------------		2eme partie (colonne)		--------------------------
echo "</td><td>\n";

// 		--------------------------		Affichage du tableau de résultat		--------------------
if ($tab) {
    //	Recherche du nombre de jours concernés
    $db=new db();
    $debutREQ=$db->escapeString($debutSQL);
    $finREQ=$db->escapeString($finSQL);
    $sitesREQ=$db->escapeString($sitesSQL);

    $db->select("pl_poste", "`date`", "`date` BETWEEN '$debutREQ' AND '$finREQ' AND `site` IN ($sitesREQ)", "GROUP BY `date`;");
    $nbJours=$db->nb;

    echo <<<EOD
  <strong>Statistiques par poste (Synthèse) du $debut au $fin</strong>
  $ouverture
  <table id='tableStatSynthese' class='CJDataTable'>
  <thead><tr>
    <th>Postes</th>
    <th class='dataTableHeureFR'>Total d'heures</th>
    <th class='dataTableHeureFR'>Moyenne jour</th>
    <th class='dataTableHeureFR'>Moyenne hebdomadaire</th>
  </tr></thead>
  <tbody>
EOD;
    foreach ($tab as $elem) {
        $class=$elem[0][3]=="Obligatoire"?"td_obligatoire":"td_renfort";
        $jour=$elem[2]/$nbJours;
        $hebdo=$jour*$joursParSemaine;
        $total_heures+=$elem[2];
        $total_jour+=$jour;
        $total_hebdo+=$hebdo;

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

        echo "<tr style='vertical-align:top;' class='$class'>\n";
        echo "<td><strong>{$elem[0][1]}</strong>\n";
        echo "<br/><i>$siteEtage</i></td>\n";
        echo "<td>".heure4($elem[2])."</td>\n";
        echo "<td>".heure4($jour)."</td>\n";
        echo "<td>".heure4($hebdo)."</td></tr>\n";
    }

    echo "</tbody>\n";
    echo "<tfooter><tr>\n";
    echo "<th><strong>Total</strong></th>\n";
    echo "<th>".heure4($total_heures)."</th>\n";
    echo "<th>".heure4($total_jour)."</th>\n";
    echo "<th>".heure4($total_hebdo)."</th></tr>\n";
    echo "</tfooter>\n";
    echo "</table>\n";
}
//		----------------------			Fin d'affichage		----------------------------
echo "</td></tr></table>\n";
echo "<script type='text/JavaScript'>document.form.tri.value='$tri';</script>\n";
