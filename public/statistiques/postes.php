<?php
/**
Planning Biblio, Version 2.7.04
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/postes.php
Création : mai 2011
Dernière modification : 22 novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche les statistiques par poste: nombre d'heures d'ouverture, moyen par jour et par semaine, nom des agents ayant occupé
le poste, nombre d'heures de chaque agent.

Page appelée par le fichier index.php, accessible par le menu statistiques / Par poste
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";
require_once "personnel/class.personnel.php";
require_once "include/horaires.php";

// Initialisation des variables :
$debut=filter_input(INPUT_POST, "debut", FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_POST, "fin", FILTER_SANITIZE_STRING);
$tri=filter_input(INPUT_POST, "tri", FILTER_SANITIZE_STRING);
$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_NUMBER_INT);

$debut=filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

$post_postes=isset($post['postes'])?$post['postes']:null;
$post_sites=isset($post['selectedSites'])?$post['selectedSites']:null;

$joursParSemaine=$config['Dimanche']?7:6;

if (!array_key_exists('stat_poste_postes', $_SESSION)) {
    $_SESSION['stat_poste_postes']=null;
    $_SESSION['stat_poste_tri']=null;
}

if (!$debut and array_key_exists("stat_debut", $_SESSION)) {
    $debut=$_SESSION['stat_debut'];
}
if (!$fin and array_key_exists("stat_fin", $_SESSION)) {
    $fin=$_SESSION['stat_fin'];
}
if (!$tri and array_key_exists("stat_poste_tri", $_SESSION)) {
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

$tab=array();

$services_tab = array();
$db = new db();
$db->select('select_services');
if($db->select){
    foreach($db->select as $elem){
        $services_tab[$elem['id']] = $elem['valeur'];
    }
}
// Récupération des infos sur les agents
$p=new personnel();
$p->fetch();
$agents_infos=$p->elements;

//		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
$db=new db();
$db->select2("postes", "*", array("statistiques"=>"1"), "ORDER BY `etage`,`nom`");
$postes_list=$db->result;

if (!empty($postes)) {
    //	Recherche du nombre de jours concernés
    $db=new db();
    $db->select2("pl_poste", "date", array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "site"=>"IN{$sitesSQL}"), "GROUP BY `date`;");
    $nbJours=$db->nb;

    // Recherche des absences dans la table absences
    $a = new absences();
    $a->valide = true;
    $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
    $absencesDB = $a->elements;

    //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
    //	On stock le tout dans le tableau $resultat
    $postes_select=join(",", $postes);
    $db=new db();

    $db->selectInnerJoin(
        array("pl_poste","perso_id"),
        array("personnel","id"),
        array("debut","fin","date","poste","site"),
        array("nom","prenom",array("name"=>"id", "as"=>"perso_id")),
        array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "poste"=>"IN{$postes_select}","absent"=>"<>1",
      "supprime"=>"<>1", "site"=>"IN{$sitesSQL}"),
        array(),
        "ORDER BY `poste`,`nom`,`prenom`"
  );

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
        $agents=array();
        $services=array();
        $statuts=array();
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

                    // on créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
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
                            $poste_tab=array($poste,$elem2['nom'],$elem2['etage'],$elem2['obligatoire']);
                            break;
                        }
                    }
                    // On créé un tableau par service
                    if (array_key_exists($elem['perso_id'], $agents_infos)) {
                        $service=$agents_infos[$elem['perso_id']]['service'];
                    }
                    $service=isset($service)?$service:"ZZZ_Autre";
                    if (!array_key_exists($service, $services)) {
                        $services[$service]=array("nom"=>$services_tab[$service],"heures"=>0);
                    }
                    $services[$service]["heures"]+=diff_heures($elem['debut'], $elem['fin'], "decimal");
      
                    // On créé un tableau par statut
                    if (array_key_exists($elem['perso_id'], $agents_infos)) {
                        $statut=$agents_infos[$elem['perso_id']]['statut'];
                    }
                    $statut=isset($statut)?$statut:"ZZZ_Autre";
                    if (!array_key_exists($statut, $statuts)) {
                        $statuts[$statut]=array("nom"=>$statut,"heures"=>0);
                    }
                    $statuts[$statut]["heures"]+=diff_heures($elem['debut'], $elem['fin'], "decimal");

                    //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
                    $tab[$poste]=array($poste_tab,$agents,$heures,"services"=>$services,"statuts"=>$statuts,"sites"=>$sites);
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
// $tab[poste_id]=Array(Array(poste_id,poste_nom,etage,obligatoire),Array[perso_id]=Array(perso_id,nom,prenom,heures),heures)
usort($tab, $tri);

// passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;

//		--------------		Affichage en 2 partie : formulaire à gauche, résultat à droite
echo "<h3>Statistiques par poste</h3>\n";
echo "<div id='statistiques'>\n";
echo "<table><tr style='vertical-align:top;'><td id='stat-col1'>\n";
//		--------------		Affichage du formulaire permettant de sélectionner les dates et les postes		-------------
echo "<form name='form' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='statistiques/postes.php' />\n";
echo "<table>\n";
echo "<tr><td><label class='intitule'>D&eacute;but</label></td>\n";
echo "<td><input type='text' name='debut' value='$debut' class='datepicker'/>\n";
echo "</td></tr>\n";
echo "<tr><td><label class='intitule'>Fin</label></td>\n";
echo "<td><input type='text' name='fin' value='$fin' class='datepicker'/>\n";
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
echo "<td><select name='postes[]' multiple='multiple' size='20' onchange='verif_select(\"postes\");' class='ui-widget-content ui-corner-all' >\n";
if (is_array($postes_list)) {
    echo "<option value='Tous'>Tous</option>\n";
    foreach ($postes_list as $elem) {
        $selected = null;
        if ($postes) {
            $selected=in_array($elem['id'], $postes)?"selected='selected'":null;
        }
        $class=$elem['obligatoire']=="Obligatoire"?"td_obligatoire":"td_renfort";
        echo "<option value='{$elem['id']}' $selected class='$class' >{$elem['nom']} ({$elem['etage']})</option>\n";
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
echo "<input type='button' value='Effacer' onclick='location.href=\"index.php?page=statistiques/postes.php&amp;debut=&amp;fin=&amp;postes=\"' class='ui-button' />\n";
echo "&nbsp;&nbsp;<input type='submit' value='OK' class='ui-button' />\n";
echo "</td></tr>\n";
echo "<tr><td colspan='2'><hr/></td></tr>\n";
echo "<tr><td>Exporter </td>\n";
echo "<td><a href='javascript:export_stat(\"postes\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"postes\",\"xsl\");'>XLS</a></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

//		--------------------------		2eme partie (colonne)		--------------------------
echo "</td><td>\n";

// 		--------------------------		Affichage du tableau de résultat		--------------------
if ($tab) {
    echo "<b>Statistiques par poste du $debut au $fin</b>\n";
    echo $ouverture;
    echo "<table border='1' cellspacing='0' cellpadding='0'>\n";
    echo "<tr class='th'>\n";
    echo "<td style='width:200px; padding-left:8px;'>Postes</td>\n";
    echo "<td style='width:300px; padding-left:8px;'>Agents</td>\n";
    echo "<td style='width:300px; padding-left:8px;'>Services</td>\n";
    echo "<td style='width:300px; padding-left:8px;'>Statuts</td></tr>\n";
    foreach ($tab as $elem) {
        $class=$elem[0][3]=="Obligatoire"?"td_obligatoire":"td_renfort";
        echo "<tr style='vertical-align:top;' class='$class'>\n";
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
        echo "<td style='padding-left:8px;'>";
        echo "<table><tr><td colspan='2'><b>{$elem[0][1]}</b></td></tr>";
        echo "<tr><td colspan='2'><i>$siteEtage</i></td></tr>\n";
        echo "<tr><td>Total</td>";
        echo "<td class='statistiques-heures'>".heure4($elem[2])."</td></tr>\n";
        $jour=$elem[2]/$nbJours;
        $hebdo=$jour*$joursParSemaine;
        echo "<tr><td>Moyenne jour</td>";
        echo "<td class='statistiques-heures'>".heure4($jour)."</td></tr>\n";
        echo "<tr><td>Moyenne hebdo.</td>";
        echo "<td class='statistiques-heures'>".heure4($hebdo)."</td></tr>\n";
        if ($config['Multisites-nombre']>1) {
            for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
                if ($elem["sites"][$i] and $elem["sites"][$i]!=$elem[2]) {
                    // Calcul des moyennes
                    $jour=$elem["sites"][$i]/$nbJours;
                    $hebdo=$jour*$joursParSemaine;
                    echo "<tr><td colspan='2' style='padding-top:20px;'><u>".$config["Multisites-site{$i}"]."</u></td></tr>";
                    echo "<tr><td>Total</td>";
                    echo "<td class='statistiques-heures'>".heure4($elem["sites"][$i])."</td></tr>";
                    ;
                    echo "<tr><td>Moyenne</td>";
                    echo "<td class='statistiques-heures'>".heure4($hebdo)."</td></tr>";
                }
            }
        }
        echo "</table>\n";
        echo "</td>\n";
        //	Affichage du noms des agents dans la 2eme colonne
        echo "<td style='padding-left:8px;'>";
        echo "<table style='width:100%;'>";
        foreach ($elem[1] as $agent) {
            echo "<tr><td>{$agent[1]} {$agent[2]}</td>";
            echo "<td class='statistiques-heures'>".heure4($agent[3])."</td></tr>\n";
        }
        echo "</table>\n";
        echo "</td>\n";
        // Services
        echo "<td>";
        sort($elem['services']);
        echo "<table style='width:100%;'>\n";
        foreach ($elem['services'] as $service) {
            echo "<tr><td>".str_replace("ZZZ_", "", $service['nom'])."</td>";
            echo "<td class='statistiques-heures'>".heure4($service['heures'])."</td></tr>";
        }
        echo "</table>\n";
        echo "</td>\n";
        // Statuts
        echo "<td>";
        sort($elem['statuts']);
        echo "<table style='width:100%;'>\n";
        foreach ($elem['statuts'] as $statut) {
            echo "<tr><td>".str_replace("ZZZ_", "", $statut['nom'])."</td>";
            echo "<td class='statistiques-heures'>".heure4($statut['heures'])."</td></tr>";
        }
        echo "</table>\n";
        echo "</td></tr>\n";
    }
    echo "</table>\n";
}
//		----------------------			Fin d'affichage		----------------------------
echo "</td></tr></table>\n";
echo "</div> <!-- Statistiques -->\n";
echo "<script type='text/JavaScript'>document.form.tri.value='$tri';</script>\n";
