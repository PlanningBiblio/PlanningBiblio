<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/fonctions.php
Création : mai 2011
Dernière modification : 25 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fonctions utilisées par les pages des dossiers planning/poste et planning/postes_cgf
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "../../include/accessDenied.php";
    exit;
}

function cellule_poste($date, $debut, $fin, $colspan, $output, $poste, $site)
{
    $resultats=array();
    $classe=array();
    $i=0;
  
    if ($GLOBALS['cellules']) {
  
    // Recherche des sans repas en dehors de la boucle pour optimiser les performances (juillet 2016)
        $p = new planning();
        $sansRepas = $p->sansRepas($date, $debut, $fin);

        foreach ($GLOBALS['cellules'] as $elem) {
            $title=null;
      
            if ($elem['poste']==$poste and $elem['debut']==$debut and $elem['fin']==$fin) {
                //		Affichage du nom et du prénom
                $nom_affiche=$elem['nom'];
                if ($elem['prenom']) {
                    $nom_affiche.=" ".substr($elem['prenom'], 0, 1).".";
                }

                $resultat = $nom_affiche;
        
                //		Affichage des sans repas
                if ($elem['nom'] and ($sansRepas === true or in_array($elem['perso_id'], $sansRepas))) {
                    $resultat.="<font class='sansRepas'>&nbsp;(SR)</font>";
                }

                $class_tmp=array();
    
                // Cellule grisée depuis le menudiv
                if (isset($elem['grise']) and $elem['grise'] == 1) {
                    $class_tmp[]= 'cellule_grise';
                }

                //		On barre les absents (agents barrés directement dans le plannings, table pl_poste)
                if ($elem['absent'] == 1 or $elem['supprime']) {
                    $class_tmp[]="red";
                    $class_tmp[]="striped";
                }

                if ($elem['depart'] > '0000-00-00' && $elem['depart'] < $date) {
                    $class_tmp[]="red";
                    $class_tmp[]="striped";
                    $title = 'Date de départ dépassée';
                }

                if ($elem['absent'] == 2) {
                    $class_tmp[] = "out-of-work-time";
                    $title = 'En dehors de ses heures de présences';
                }

                // On marque les absents (absences enregistrées dans la table absences)
                $absence_valide = false;
                
                foreach ($GLOBALS['absences'] as $absence) {

                    // Skip teleworking absences if the position is compatible with
                    if ($GLOBALS['postes'][$poste]['teleworking']) {
                        $reason = $GLOBALS['absence_reasons']->findOneBy(array('valeur' => $absence['motif']));
                        if (!empty($reason) and $reason->teleworking() == 1) {
                            continue;
                        }
                    }

                    if ($absence["perso_id"] == $elem['perso_id'] and $absence['debut'] < $date." ".$fin and $absence['fin'] > $date." ".$debut) {
                        // Absence validée : rouge barré
                        if ($absence['valide']>0 or $GLOBALS['config']['Absences-validation'] == 0) {
                            $class_tmp[]="red";
                            $class_tmp[]="striped";
                            $absence_valide = true;
                            break;  // Garder le break à cet endroit pour que les absences validées prennent le dessus sur les non-validées
                        }
                        // Absence non-validée : rouge
                        elseif ($GLOBALS['config']['Absences-non-validees']) {
                            $class_tmp[]="red";
                            $title = $nom_affiche.' : Absence non-valid&eacute;e';
                        }
                    }
                }
    
                // Il peut y avoir des absences validées et non validées. Si ce cas ce produit, la cellule sera barrée et on n'affichera pas "Absence non-validée"
                if ($absence_valide) {
                    $title=null;
                }
    
    
                //		On barre les congés
                if ($GLOBALS['config']['Conges-Enable']) {
                    include "conges/planning_cellule_poste.php";
                }
      
                // Classe en fonction du statut et du service
                if ($elem['statut']) {
                    $class_tmp[]="statut_".strtolower(removeAccents(str_replace(" ", "_", $elem['statut'])));
                }
                if ($elem['service']) {
                    $class_tmp[]="service_".strtolower(removeAccents(str_replace(" ", "_", $elem['service'])));
                }
                if (isset($elem['activites']) and is_array($elem['activites'])) {
                    foreach ($elem['activites'] as $a) {
                        $class_tmp[]='activite_'.strtolower(removeAccents(str_replace(array('/',' ',), '_', $a)));
                    }
                }
                $classe[$i]=implode(" ", $class_tmp);

                // Création d'une balise span avec les classes cellSpan, et agent_ de façon à les repérer et agir dessus à partir de la fonction JS bataille_navale.
                $span="<span class='cellSpan agent_{$elem['perso_id']}' title='$title' >$resultat</span>";

                $resultats[$i]=array("text"=>$span, "perso_id"=>$elem['perso_id']);
                $i++;
            }
        }
    }
    $GLOBALS['idCellule']++;
    $cellule="<td id='td{$GLOBALS['idCellule']}' colspan='$colspan' style='text-align:center;' class='menuTrigger' 
    oncontextmenu='cellule={$GLOBALS['idCellule']}'
    data-start='$debut' data-end='$fin' data-situation='$poste' data-cell='{$GLOBALS['idCellule']}' data-perso-id='0'>";
    for ($i=0;$i<count($resultats);$i++) {
        $cellule.="<div id='cellule{$GLOBALS['idCellule']}_$i' class='cellDiv {$classe[$i]} pl-cellule-perso-{$resultats[$i]['perso_id']}' data-perso-id='{$resultats[$i]['perso_id']}'>{$resultats[$i]['text']}</div>";
    }

    $cellule .= '<a class="pl-icon arrow-right" href="#"></a>';
    $cellule.="</td>\n";
    return $cellule;
}

function deja_place($date, $poste)
{
    $deja=array(0);
    $db=new db();
    $db->select2("pl_poste", "perso_id", array("date"=>$date, "absent"=>"0", "poste"=>$poste), "GROUP BY `perso_id`");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $deja[]=$elem['perso_id'];
        }
    }
    return $deja;
}

function deuxSP($date, $debut, $fin)
{
    $tab=array(0);
    $db=new db();
    $db->select("pl_poste", "perso_id", "absent = '0' AND date='$date' AND (debut='$fin' OR fin='$debut')", "group by perso_id");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $tab[]=$elem['perso_id'];
        }
    }
    return $tab;
}

//--------		Vérifier si le poste demandé appartient à un groupe, si oui, on recherche les personnes qualifiées pour ce groupe (poste=groupe) --------//
function groupe($poste)
{
    $db=new db();
    $db->query("SELECT `groupe_id` FROM `{$GLOBALS['config']['dbprefix']}postes` WHERE `id`='$poste';");
    if ($db->result and $db->result[0]['groupe_id']!=0) {
        $poste=$db->result[0]['groupe_id'];
    }
    return $poste;
}
//--------		FIN Vérifier si le poste demandé appartient à un groupe, si oui, on recherche les personnes qualifiées pour ce groupe (poste=groupe) ---------//


// Vérifie si la ligne du tableau correspondant au poste $poste est vide ou non
function isAnEmptyLine($poste)
{
    if (!$poste) {
        return false;
    }
    foreach ($GLOBALS['cellules'] as $elem) {
        if ($poste==$elem['poste']) {
            return false;
        }
    }
    return true;
}


//		-------------	paramétrage de la largeur des colonnes		--------------//
function nb30($debut, $fin)
{
    $tmpFin=explode(":", $fin);
    $tmpDebut=explode(":", $debut);
    $time=(($tmpFin[0]*60)+$tmpFin[1]-($tmpDebut[0]*60)-$tmpDebut[1])/15;
    return $time;
}
//		-------------	FIN paramétrage de la largeur des colonnes		--------------//
