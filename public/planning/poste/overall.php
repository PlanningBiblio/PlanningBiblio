<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/index.php
Création : mai 2011
Dernière modification : 7 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Farid Goara <farid.goara@u-pem.fr>


Description :
Cette page affiche le planning. Par défaut, le planning du jour courant est affiché. On peut choisir la date voulue avec le
calendrier ou les jours de la semaine.

Cette page est appelée par la page index.php
*/

use App\Model\HiddenSites;

require_once "class.planning.php";
require_once __DIR__."/../volants/class.volants.php";
include_once "absences/class.absences.php";
include_once __DIR__ . "/../../conges/class.conges.php";
include_once "activites/class.activites.php";
include_once "personnel/class.personnel.php";

echo '<script src="js/overall.js"></script>';
echo '<link rel="StyleSheet" href="themes/default/overall.css">';

echo "<div id='plannings'>\n";

include "fonctions.php";

use PlanningBiblio\PresentSet;
use App\PlanningBiblio\Framework;

// Initialisation des variables
$date=filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);
$login_id = $_SESSION['login_id'];

// Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
$date=filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));

//		------------------		DATE		-----------------------//
if (!$date and array_key_exists('PLdate', $_SESSION)) {
    $date=$_SESSION['PLdate'];
} elseif (!$date and !array_key_exists('PLdate', $_SESSION)) {
    $date=date("Y-m-d");
}
$_SESSION['PLdate']=$date;
$d=new datePl($date);
$semaine=$d->semaine;
$semaine3=$d->semaine3;
$jour=$d->jour;
$dates=$d->dates;
$datesSemaine=join(",", $dates);
$j1=$dates[0];
$j2=$dates[1];
$j3=$dates[2];
$j4=$dates[3];
$j5=$dates[4];
$j6=$dates[5];
$j7=$dates[6];
$dateAlpha=dateAlpha($date);

$_SESSION['oups']['week']=false;

global $idCellule;
$idCellule=0;

//		---------------		changement de couleur du menu et de la periode en fonction du jour sélectionné	---------//
$class=array('menu','menu','menu','menu','menu','menu','menu','menu');

switch ($jour) {
  case "lun":	$jour3="Lundi";		$periode2='semaine';	$class[0]='menuRed';	break;
  case "mar":	$jour3="Mardi";		$periode2='semaine';	$class[1]='menuRed';	break;
  case "mer":	$jour3="Mercredi";	$periode2='semaine';	$class[2]='menuRed';	break;
  case "jeu":	$jour3="Jeudi";		$periode2='semaine';	$class[3]='menuRed';	break;
  case "ven":	$jour3="Vendredi";	$periode2='semaine';	$class[4]='menuRed';	break;
  case "sam":	$jour3="Samedi";	$periode2='samedi';	$class[5]='menuRed';	break;
  case "dim":	$jour3="Dimanche";	$periode2='samedi';	$class[6]='menuRed';	break;
}
    
//	---------------		FIN changement de couleur du menu et de la periode en fonction du jour sélectionné	--------------------------//

//	Selection des messages d'informations
$db=new db();
$db->select2("infos", "*", array("debut"=>"<={$date}", "fin"=>">={$date}"), "ORDER BY `debut`,`fin`");
$messages_infos=null;
if ($db->result) {
    foreach ($db->result as $elem) {
        $messages_infos[]=$elem['texte'];
    }
    $messages_infos=join($messages_infos, " - ");
}

//		---------------		Affichage du titre et du calendrier	--------------------------//
echo "<div id='divcalendrier' class='text'>\n";

echo "<form name='form' method='get' action='#'>\n";
echo "<input type='hidden' id='date' name='date' value='$date' data-set-calendar='$date' />\n";
echo "<input type='hidden' id='login_id' value='$login_id' />\n";
echo "</form>\n";

echo "<table id='tab_titre'>\n";
echo "<tr><td><div class='noprint'>\n";
?>
<div id='pl-calendar' class='datepicker'></div>
<?php
echo "</div></td><td class='titreSemFixe'>\n";
echo "<div class='noprint'>\n";

switch ($config['nb_semaine']) {
  case 2:	$type_sem=$semaine%2?"Impaire":"Paire";	$affSem="$type_sem ($semaine)";	break;
  case 3: 	$type_sem=$semaine3;			$affSem="$type_sem ($semaine)";	break;
  default:	$affSem=$semaine;	break;
}
echo "<b>Semaine $affSem</b>\n";
echo "</div>";
echo "<div id='semaine_planning'><b>Du ".dateFr($j1)." au ".dateFr($j7)."</b>\n";
echo "</div>\n";
echo "<div id='date_planning'>Planning du $dateAlpha";
if (jour_ferie($date)) {
    echo " - <font id='ferie'>".jour_ferie($date)."</font>";
}
echo <<<EOD
  </div>
  <table class='noprint' id='tab_jours'><tr valign='top'>
    <td><a href='index.php?page=planning/poste/overall.php&date=$j1'  class='{$class[0]}' >Lundi</a> / </td>
    <td><a href='index.php?page=planning/poste/overall.php&date=$j2'  class='{$class[1]}' >Mardi</a> / </td>
    <td><a href='index.php?page=planning/poste/overall.php&date=$j3'  class='{$class[2]}' >Mercredi</a> / </td>
    <td><a href='index.php?page=planning/poste/overall.php&date=$j4'  class='{$class[3]}' >Jeudi</a> / </td>
    <td><a href='index.php?page=planning/poste/overall.php&date=$j5'  class='{$class[4]}' >Vendredi</a> / </td>
    <td><a href='index.php?page=planning/poste/overall.php&date=$j6'  class='{$class[5]}' >Samedi</a></td>
EOD;
if ($config['Dimanche']) {
    echo "<td align='center'> / <a href='index.php?page=planning/poste/overall.php&date=$j7'  class='".$class[6]."' >Dimanche</a> </td>";
}

echo "</tr></table>";
  
//	---------------------		Affichage des messages d'informations		-----------------//
echo "<div id='messages_infos'>\n";
echo "<marquee>\n";
echo $messages_infos;
echo "</marquee>\n";
echo "</div>";

echo "</td><td id='td_boutons'>\n";

//	----------------------------	Récupération des postes		-----------------------------//
$postes=array();

// Récupération des activités pour appliquer les classes aux lignes postes en fonction de celles-ci
$a=new activites();
$a->deleted=true;
$a->fetch();
$activites=$a->elements;

// Récupération des catégories pour appliquer les classes aux lignes postes en fonction de celles-ci
$categories=array();
$db=new db();
$db->select2("select_categories");
if ($db->result) {
    foreach ($db->result as $elem) {
        $categories[$elem['id']]=$elem['valeur'];
    }
}

// Récupération des postes
$db=new db();
$db->select2("postes", "*", "1", "ORDER BY `id`");

if ($db->result) {
    foreach ($db->result as $elem) {
    
    // Classes CSS du poste
        $classesPoste=array();

        // Ajout des classes en fonction des activités
        $activitesPoste = $elem['activites'] ? json_decode(html_entity_decode($elem['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
        foreach ($activitesPoste as $a) {
            if (isset($activites[$a]['nom'])) {
                $classesPoste[] = 'tr_activite_'.strtolower(removeAccents(str_replace(array(' ','/'), '_', $activites[$a]['nom'])));
            }
        }
    
        // Ajout des classes de la ligne en fonction des catégories requises par le poste (A,B ou C)
        $categoriesPoste = $elem['categories'] ? json_decode(html_entity_decode($elem['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
        foreach ($categoriesPoste as $cat) {
            if (array_key_exists($cat, $categories)) {
                $classesPoste[]="tr_".str_replace(" ", "", removeAccents(html_entity_decode($categories[$cat], ENT_QUOTES|ENT_IGNORE, "UTF-8")));
            }
        }
    

        // Tableau $postes
        $postes[$elem['id']]=array("nom"=>$elem['nom'], "etage"=>$elem['etage'], "obligatoire"=>$elem['obligatoire'], "classes"=>join(" ", $classesPoste));
    }
}

//	-----------------------		FIN Récupération des postes	-----------------------------//

echo "<div id='planningTips'>&nbsp;</div>";
echo "</td></tr>\n";

//----------------------	FIN Verrouillage du planning		-----------------------//
echo "</table></div>\n";

$entityManager = $GLOBALS['entityManager'];
$hidden_sites = $entityManager
    ->getRepository(HiddenSites::class)
    ->findOneBy(array('perso_id' => $login_id));
$hidden_sites = $hidden_sites ? explode(';', $hidden_sites->hidden_sites()) : array();

$nb_sites = $config['Multisites-nombre'];

// START SITE PLANNING
$site = 1;
while ($site <= $nb_sites) {

    $db=new db();
    $db->select2("pl_poste_verrou", "*", array("date"=>$date, "site"=>$site));
    $verrou = 0;
    if ($db->result) {
        $verrou=$db->result[0]['verrou2'];
    }

    $can_see = (in_array((300+$site), $droits) or in_array((1000+$site), $droits));
    if ($verrou) {
        $can_see = 1;
    }
    if (!$can_see) {
        $site++;
        continue;
    }

    $site_name = $config["Multisites-site$site"];
    echo "<h3>";
    if ($verrou) {
        echo "<span class='pl-icon pl-icon-lock noprint' title='Le planning est validé'></span>";
    } else {
        echo '<span class="pl-icon pl-icon-unlock noprint" title="Le planning n\'est pas validé"></span>';
    }

    if ($nb_sites > 1) {
        echo "Planning du site: $site_name";
        echo "<span title='Masquer le site $site_name' class='pl-icon pl-icon-hide hideSite pointer' data-site='$site' data-site-name='$site_name'></span>";
    }
    echo "</h3>";

    $db=new db();
    $db->select2("pl_poste_tab_affect", "tableau", array("date"=>$date, "site"=>$site));
    $tab = isset($db->result[0]['tableau']) ? $db->result[0]['tableau'] : '' ;
    if (!$tab) {
        if (in_array($site, $hidden_sites)) {
            echo "<div id='planning_$site' style='display:none;'>Aucun planning pour ce site.</div>";
        } else {
            echo "<div id='planning_$site'>Aucun planning pour ce site.</div>";
        }
        $site++;
        continue;
    }

    //--------------	Recherche des infos cellules	------------//
    // Toutes les infos seront stockées danx un tableau et utilisées par les fonctions cellules_postes
    $db=new db();
    $db->selectLeftJoin(
        array("pl_poste","perso_id"),
        array("personnel","id"),
        array("perso_id","debut","fin","poste","absent","supprime","grise"),
        array("nom","prenom","statut","service","postes"),
        array("date"=>$date, "site"=>$site),
        array(),
        "ORDER BY `{$dbprefix}personnel`.`nom`, `{$dbprefix}personnel`.`prenom`"
    );

    global $cellules;
    $cellules=$db->result?$db->result:array();
    usort($cellules, "cmp_nom_prenom");

    // Recherche des agents volants
    if ($config['Planning-agents-volants']) {
        $v = new volants($date);
        $v->fetch($date);
        $agents_volants = $v->selected;

        // Modification du statut pour les agents volants afin de personnaliser l'affichage
        foreach ($cellules as $k => $v) {
            if (in_array($v['perso_id'], $agents_volants)) {
                $cellules[$k]['statut'] = 'volants';
            }
        }
    }

    // Recherche des absences
    // Le tableau $absences sera utilisé par la fonction cellule_poste pour barrer les absents dans le plannings et pour afficher les absents en bas du planning
    $a=new absences();
    $a->valide=false;
    $a->agents_supprimes = array(0,1,2);    // required for history
    $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
    $absences=$a->elements;
    global $absences;

    // Ajoute les qualifications de chaque agent (activités) dans le tableaux $cellules pour personnaliser l'affichage des cellules en fonction des qualifications
    foreach ($cellules as $k => $v) {
        if ($v['postes']) {
            $p = json_decode(html_entity_decode($v['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $cellules[$k]['activites'] = array();
            foreach ($activites as $elem) {
                if (in_array($elem['id'], $p)) {
                    $cellules[$k]['activites'][] = $elem['nom'];
                }
            }
        }
    }

    // Tri des absences par nom
    usort($absences, "cmp_nom_prenom_debut_fin");

    // Affichage des absences en bas du planning : absences concernant le site choisi
    $a=new absences();
    $a->valide=false;
    $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date, array($site));
    $absences_planning = $a->elements;

    // Informations sur les congés
    $conges = array();
    global $conges;
    if ($config['Conges-Enable']) {
        $c = new conges();
        $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
    }
    //--------------	FIN Recherche des infos cellules	------------//


    //	------------		Affichage du tableau			--------------------//
    //	Lignes de separation
    $db=new db();
    $db->select2("lignes");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $lignes_sep[$elem['id']]=$elem['nom'];
        }
    }

    // Récupération de la structure du tableau
    $t = new Framework();
    $t->id=$tab;
    $t->get();
    $tabs=$t->elements;

    // Repère les heures de début et de fin de chaque tableau pour ajouter des colonnes si ces heures sont différentes
    $debut="23:59";
    $fin=null;
    foreach ($tabs as $elem) {
        $debut=$elem["horaires"][0]["debut"]<$debut?$elem["horaires"][0]["debut"]:$debut;
        $nb=count($elem["horaires"])-1;
        $fin=$elem["horaires"][$nb]["fin"]>$fin?$elem["horaires"][$nb]["fin"]:$fin;
    }

    // affichage du tableau :
    if (in_array($site, $hidden_sites)) {
        echo "<div id='planning_$site' data-tableId='$tab' style='display: none;' >\n";
    } else {
        echo "<div id='planning_$site' data-tableId='$tab' >\n";
    }
    // affichage de la lignes des horaires
    echo "<table id='tabsemaine1' cellspacing='0' cellpadding='0' class='text tabsemaine1'>\n";


    // Tableaux masqués
    $hiddenTables = array();
    $db=new db();
    $db->select2("hidden_tables", "*", array("perso_id"=>$_SESSION['login_id'],"tableau"=>$tab));
    if ($db->result) {
        $hiddenTables = json_decode(html_entity_decode($db->result[0]['hidden_tables'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
    }

    // $sn : index des tableaux Sans nom pour l'affichage des tableaux masqués
    $sn=1;

    $j=0;
    foreach ($tabs as $tab) {
        $hiddenTable = in_array($j, $hiddenTables) ? 'hidden-table' :null;

        // Comble les horaires laissés vides : créé la colonne manquante, les cellules de cette colonne seront grisées
        $cellules_grises=array();
        $tmp=array();

        // Première colonne : si le début de ce tableau est supérieur au début d'un autre tableau
        $k=0;
        if ($tab['horaires'][0]['debut']>$debut) {
            $tmp[]=array("debut"=>$debut, "fin"=>$tab['horaires'][0]['debut']);
            $cellules_grises[]=$k++;
        }

        // Colonnes manquantes entre le début et la fin
        foreach ($tab['horaires'] as $key => $value) {
            if ($key==0 or $value["debut"]==$tab['horaires'][$key-1]["fin"]) {
                $tmp[]=$value;
            } elseif ($value["debut"]>$tab['horaires'][$key-1]["fin"]) {
                $tmp[]=array("debut"=>$tab['horaires'][$key-1]["fin"], "fin"=>$value["debut"]);
                $tmp[]=$value;
                $cellules_grises[]=$k++;
            }
            $k++;
        }

        // Dernière colonne : si la fin de ce tableau est inférieure à la fin d'un autre tableau
        $nb=count($tab['horaires'])-1;
        if ($tab['horaires'][$nb]['fin']<$fin) {
            $tmp[]=array("debut"=>$tab['horaires'][$nb]['fin'], "fin"=>$fin);
            $cellules_grises[]=$k;
        }


        $tab['horaires']=$tmp;

        // Titre des tableaux, ajoute les intutulés "Sans nom x" pour les tableaux qui ne sont pas nommés afin de pouvoir les afficher s'ils sont cachés
        $tab['titre2'] = $tab['titre'];
        if (!$tab['titre']) {
            $tab['titre2'] = "Sans nom $sn";
            $sn++;
        }

        // Masquer les tableaux
        $masqueTableaux=null;
        if ($config['Planning-TableauxMasques']) {
            $masqueTableaux="<span title='Masquer' class='pl-icon pl-icon-hide masqueTableau pointer noprint' data-id='$j' ></span>";
        }

        //		Lignes horaires
        echo "<tr class='tr_horaires tableau$j {$tab['classe']} $hiddenTable'>\n";
        echo "<td class='td_postes' data-id='$j' data-title='{$tab['titre2']}'>{$tab['titre']} $masqueTableaux </td>\n";
        $colspan=0;
        foreach ($tab['horaires'] as $horaires) {
            echo "<td colspan='".nb30($horaires['debut'], $horaires['fin'])."'>".heure3($horaires['debut'])."-".heure3($horaires['fin'])."</td>";
            $colspan+=nb30($horaires['debut'], $horaires['fin']);
        }
        echo "</tr>\n";

        //	Lignes postes et grandes lignes
        foreach ($tab['lignes'] as $ligne) {
            // Regardons si la ligne est vide afin de ne pas l'afficher si $config['Planning-lignes-vides']=0
            $emptyLine=null;
            if (!$config['Planning-lignesVides'] and isAnEmptyLine($ligne['poste'])) {
                $emptyLine="empty-line";
            }

            // Lignes postes
            if ($ligne['type']=="poste" and $ligne['poste']) {
                // Classe de la première cellule en fonction du type de poste (obligatoire ou de renfort)
                $classTD = $postes[$ligne['poste']]['obligatoire'] == "Obligatoire" ? "td_obligatoire" : "td_renfort";
                // Classe de la ligne en fonction du type de poste (obligatoire ou de renfort)
                $classTR = $postes[$ligne['poste']]['obligatoire'] == "Obligatoire" ? "tr_obligatoire" : "tr_renfort";

                // Classe de la ligne en fonction des activités et des catégories
                $classTR .= ' ' . $postes[$ligne['poste']]['classes'];

                // Affichage de la ligne
                echo "<tr class='pl-line tableau$j $classTR {$tab['classe']} $hiddenTable $emptyLine'>\n";
                echo "<td class='td_postes $classTD'>{$postes[$ligne['poste']]['nom']}";
                // Affichage ou non des étages
                if ($config['Affichage-etages'] and $postes[$ligne['poste']]['etage']) {
                    echo " ({$postes[$ligne['poste']]['etage']})";
                }
                echo "</td>\n";
                $i=1;
                $k=1;
                foreach ($tab['horaires'] as $horaires) {
                    // Recherche des infos à afficher dans chaque cellule
                    // Cellules grisées si définies dans la configuration du tableau et si la colonne a été ajoutée automatiquement
                    if (in_array("{$ligne['ligne']}_{$k}", $tab['cellules_grises']) or in_array($i-1, $cellules_grises)) {
                        echo "<td colspan='".nb30($horaires['debut'], $horaires['fin'])."' class='cellule_grise'>&nbsp;</td>";
                        // Si colonne ajoutée, ça décale les cellules grises initialement prévues. On se décale d'un cran en arrière pour rétablir l'ordre
                        if (in_array($i-1, $cellules_grises)) {
                            $k--;
                        }
                    }
                    // fonction cellule_poste(date,debut,fin,colspan,affichage,poste,site)
                    else {
                        echo cellule_poste($date, $horaires["debut"], $horaires["fin"], nb30($horaires['debut'], $horaires['fin']), "noms", $ligne['poste'], $site);
                    }
                    $i++;
                    $k++;
                }
                echo "</tr>\n";
            }
            // Lignes de séparation
            if ($ligne['type']=="ligne") {
                echo "<tr class='tr_separation tableau$j {$tab['classe']} $hiddenTable'>\n";
                echo "<td>{$lignes_sep[$ligne['poste']]}</td><td colspan='$colspan'>&nbsp;</td></tr>\n";
            }
        }
        echo "<tr class='tr_espace tableau$j {$tab['classe']} $hiddenTable'><td>&nbsp;</td></tr>\n";
        $j++;
    }
    echo "</table>\n";
    echo "</div>\n";
    // END SITE PLANNING
    $site++;
}
?>
</div>
</div>
