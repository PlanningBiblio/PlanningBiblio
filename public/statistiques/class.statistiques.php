<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : statistiques/class.statistiques.php
Création : 16 janvier 2013
Dernière modification : 20 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe statistiques

Utilisée par les fichiers du dossier "statistiques"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
$version = $GLOBALS['version'] ?? null;

if (!isset($version)) {
    include_once "../include/accessDenied.php";
}

// AJouter les html_entity_decode latin1
// AJouter les variables $nom, (agents,service,statut)

function statistiques1($nom, $tab, $debut, $fin, $separateur, $nbJours, $joursParSemaine)
{
    $titre="Statistiques par $nom du $debut au $fin";

    $lignes=array($titre,null,"Postes");
    if ($nom=="agent") {
        $cellules=array("Nom","Prénom","Heures","Moyenne hebdo");
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
                $cellules[]="Heures ".$GLOBALS['config']["Multisites-site{$i}"];
                $cellules[]="Moyenne ".$GLOBALS['config']["Multisites-site{$i}"];
            }
        }
        $cellules[]="Poste";
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $cellules[]="Site";
        }
        $cellules=array_merge($cellules, array("Etage","Heures par poste"));
        $lignes[]=join($separateur, $cellules);
    } else {
        $cellules=array(ucfirst($nom),"Heures","Moyenne hebdo");
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
                $cellules[]="Heures ".$GLOBALS['config']["Multisites-site{$i}"];
                $cellules[]="Moyenne ".$GLOBALS['config']["Multisites-site{$i}"];
            }
        }
        $cellules[]="Poste";
        if ($GLOBALS['config']['Multisites-nombre']>1) {
            $cellules[]="Site";
        }
        $cellules=array_merge($cellules, array("Etage","Heures par poste"));
        $lignes[]=join($separateur, $cellules);
    }
    foreach ($tab as $elem) {
        $jour=$elem[2]/$nbJours;
        $hebdo=$jour*$joursParSemaine;
        foreach ($elem[1] as $poste) {
            $cellules=array();
            if ($nom=="agent") {
                $cellules[]=$elem[0][1];	// nom
    $cellules[]=$elem[0][2];	// prénom
            } else {
                $cellules[]=$elem[0];		// nom du service ou du statut
            }
            $cellules[]=number_format($elem[2], 2, ',', ' ');			// Nombre d'heures
      $cellules[]=number_format($hebdo, 2, ',', ' ');		// moyenne hebdo
      if ($GLOBALS['config']['Multisites-nombre']>1) {
          for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
              $jour=$elem["sites"][$i]/$nbJours;
              $hebdo=$jour*$joursParSemaine;
              $cellules[]=number_format($elem["sites"][$i], 2, ',', ' ');
              $cellules[]=number_format($hebdo, 2, ',', ' ');
          }
      }
            $cellules[]=$poste[1];						// Nom du poste
            $site=null;
            if ($poste["site"]>0 and $GLOBALS['config']['Multisites-nombre']>1) {
                $site=$GLOBALS['config']["Multisites-site{$poste['site']}"]." ";
            }
            if ($GLOBALS['config']['Multisites-nombre']>1) {
                $cellules[]=$site;
            }
            $cellules[]=$poste[2];						// Etage
      $cellules[]=number_format($poste[3], 2, ',', ' ');			// Heures par poste
      $lignes[]=join($separateur, $cellules);
        }
    }
    $lignes[]=null;
    $lignes[]="Samedis";
  
    if ($nom=="agent") {
        $lignes[]=join($separateur, array("Nom","Prénom","Nombre de samedis","Dates","Heures"));
    } else {
        $lignes[]=join($separateur, array(ucfirst($nom),"Nombre de samedis","Dates","Heures"));
    }
    foreach ($tab as $elem) {
        foreach ($elem[3] as $samedi) {
            $cellules=array();
            if ($nom=="agent") {
                $cellules[]=$elem[0][1];	// nom
    $cellules[]=$elem[0][2];	// prénom
            } else {
                $cellules[]=$elem[0];		// nom du service ou du statut
            }
            $cellules[]=count($elem[3]);						// nombre de samedi
      $cellules[]=dateFr($samedi[0]);						// date
      $cellules[]=number_format($samedi[1], 2, ',', ' ');	// heures
      $lignes[]=join($separateur, $cellules);
        }
    }
    if ($GLOBALS['config']['Dimanche']) {
        $lignes[]=null;
        $lignes[]="Dimanches";
        if ($nom=="agent") {
            $lignes[]=join($separateur, array("Nom","Prénom","Nombre de dimanches","Dates","Heures"));
        } else {
            $lignes[]=join($separateur, array(ucfirst($nom),"Nombre de dimanches","Dates","Heures"));
        }
        foreach ($tab as $elem) {
            foreach ($elem[6] as $dimanche) {
                $cellules=array();
                if ($nom=="agent") {
                    $cellules[]=$elem[0][1];	// nom
      $cellules[]=$elem[0][2];	// prénom
                } else {
                    $cellules[]=$elem[0];		// nom du service ou du statut
                }
                $cellules[]=count($elem[6]);						// nombre de dimanche
    $cellules[]=dateFr($dimanche[0]);						// date
    $cellules[]=number_format($dimanche[1], 2, ',', ' ');	// heures
    $lignes[]=join($separateur, $cellules);
            }
        }
    }

    //		Affichage des jours feries
    $lignes[]=null;
    $lignes[]="Jours fériés";
    if ($nom=="agent") {
        $lignes[]=join($separateur, array("Nom","Prénom","Nombre de jours feriés","Dates","Heures"));
    } else {
        $lignes[]=join($separateur, array(ucfirst($nom),"Nombre de jours feriés","Dates","Heures"));
    }
    foreach ($tab as $elem) {
        foreach ($elem[8] as $ferie) {
            $cellules=array();
            if ($nom=="agent") {
                $cellules[]=$elem[0][1];	// nom
    $cellules[]=$elem[0][2];	// prénom
            } else {
                $cellules[]=$elem[0];		// nom du service ou du statut
            }
            $cellules[]=count($elem[8]);						// nombre de J. Feriés
      $cellules[]=dateFr($ferie[0]);						// date
      $cellules[]=number_format($ferie[1], 2, ',', ' ');	// heures
      $lignes[]=join($separateur, $cellules);
        }
    }

    // Absences
    $lignes[]=null;
    $lignes[]="Absences";
    if ($nom=="agent") {
        $lignes[]=join($separateur, array("Nom","Prénom","Heures d'absences","Dates","Heures"));
    } else {
        $lignes[]=join($separateur, array(ucfirst($nom),"Heures d'absences","Dates","Heures"));
    }
    foreach ($tab as $elem) {
        $total_absences=$elem[5];
        foreach ($elem[4] as $absences) {
            $cellules=array();
            if ($nom=="agent") {
                $cellules[]=$elem[0][1];	// nom
    $cellules[]=$elem[0][2];	// prénom
            } else {
                $cellules[]=$elem[0];		// nom du service ou du statut
            }
            $cellules[]=number_format($total_absences, 2, ',', ' ');
            ;						// heures total d'absences
      $cellules[]=dateFr($absences[0]);					// date
      $cellules[]=number_format($absences[1], 2, ',', ' ');	// heures
      $lignes[]=join($separateur, $cellules);
        }
    }

    //	Affichage des statistiques sur les créneaux horaires
    $heures = array();
    foreach ($tab as $elem) {
        foreach ($elem[7] as $k => $v) {
            $heures[$k][] = $v;
        }
    }
  
    ksort($heures);
  
    foreach ($heures as $k1 => $v1) {
        $lignes[]=null;
        $tmp = explode('-', $k1);
        $hres = heure3($tmp[0]).'-'.heure3($tmp[1]);
        $lignes[] = $hres;

        if ($nom=="agent") {
            $lignes[]=join($separateur, array("Nom","Prénom","Nombre de $hres","Dates"));
        } else {
            $lignes[]=join($separateur, array(ucfirst($nom),"Nombre de $hres","Dates","Nombre à cette date"));
        }
    
    
        foreach ($tab as $elem) {
            foreach ($elem[7] as $k => $v) {
                $count = 0;
                if ($k == $k1) {
                    foreach ($v as $e) {
                        $count++;
                    }
          
                    if ($nom=="agent") {
                        foreach ($v as $e) {
                            $cellules=array();
                            $cellules[]=$elem[0][1];	// nom
              $cellules[]=$elem[0][2];	// prénom
              $cellules[]=number_format($count, 2, ',', ' ');
                            $cellules[]=dateFr($e);
                            $lignes[]=join($separateur, $cellules);
                        }
                    } else {
                        // $count2 permet de n'afficher qu'une ligne par date et de compter les occurences correspondantes
                        $count2 = array();
                        foreach ($v as $e) {
                            $count2[$e] = empty($count2[$e]) ? 1 : $count2[$e] = $count2[$e] +1;
                        }
            
                        $keys = array_keys($count2);
                        sort($keys);
            
                        foreach ($keys as $e) {
                            $cellules=array();
                            $cellules[]=$elem[0];	// nom du service ou du statut
                            $cellules[]=number_format($count, 2, ',', ' ');
                            $cellules[]=dateFr($e);
                            $cellules[]=number_format($count2[$e], 2, ',', ' ');
                            $lignes[]=join($separateur, $cellules);
                        }
                    }
                }
            }
        }
    }

    return $lignes;
}

function statistiquesSamedis($tab, $debut, $fin, $separateur, $nbJours, $joursParSemaine)
{
    $titre="Statistiques sur les samedis travaillés du $debut au $fin";
    $lignes=array($titre,null);

    $cellules=array("Nom","Prénom","Prime / Temps","Nombre","Total d'heures","Dates","Heures");
    $lignes[]=join($separateur, $cellules);
  
    foreach ($tab as $elem) {
        $heures=0;
        foreach ($elem[3] as $samedi) {
            $heures+=$samedi[1];
        }
        foreach ($elem[3] as $samedi) {
            $cellules=array();
            $cellules[]=removeAccents($elem[0][1]);	// nom
      $cellules[]=removeAccents($elem[0][2]);	// prénom
      $cellules[]=$elem[0][3];	// Prime / Temps (champ récup de la fiche agent)
      $cellules[]=count($elem[3]);	// nombre de samedi
      $cellules[]=number_format($heures, 2, ',', ' ');	// Total d'heures
      $cellules[]=dateFr($samedi[0]);						// date
      $cellules[]=number_format($samedi[1], 2, ',', ' ');	// heures
      $lignes[]=join($separateur, $cellules);
        }
    }
  
    //	Affichage des statistiques sur les créneaux horaires
    $heures = array();
    foreach ($tab as $elem) {
        foreach ($elem[7] as $k => $v) {
            $heures[$k][] = $v;
        }
    }
  
    ksort($heures);
  
    foreach ($heures as $k1 => $v1) {
        $lignes[]=null;
        $tmp = explode('-', $k1);
        $hres = heure3($tmp[0]).'-'.heure3($tmp[1]);
        $lignes[] = $hres;

        $lignes[]=join($separateur, array("Nom","Prénom","Nombre de $hres","Dates"));

        foreach ($tab as $elem) {
            foreach ($elem[7] as $k => $v) {
                $count = 0;
                if ($k == $k1) {
                    foreach ($v as $e) {
                        $count++;
                    }
          
                    foreach ($v as $e) {
                        $cellules=array();
                        $cellules[]=removeAccents($elem[0][1]);	// nom
            $cellules[]=removeAccents($elem[0][2]);	// prénom
            $cellules[]=number_format($count, 2, ',', ' ');
                        $cellules[]=dateFr($e);
                        $lignes[]=join($separateur, $cellules);
                    }
                }
            }
        }
    }
  
    return $lignes;
}

class statistiques
{
    public $debut=null;
    public $fin=null;
    public $joursParSemaine=null;
    public $selectedSites=null;

    public function ouverture()
    {

    // Recherche du nombre d'heures, de jours et de semaine d'ouverture au public par site
        $debut=$this->debut;
        $fin=$this->fin;
        $joursParSemaine=$this->joursParSemaine;
        $selectedSites=$this->selectedSites?$this->selectedSites:array(1);
        $totalHeures=array();
        $totalJours=array();
        $totalSemaines=array();

        if ($GLOBALS['config']['Multisites-nombre']>1 and is_array($selectedSites)) {
            $reqSites="AND `site` IN (0,".join(",", $selectedSites).")";
        } else {
            $reqSites=null;
        }

        for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
            // Nombre d'heures
            $totalHeures[$i]=0;
            $db=new db();
            $db->select("pl_poste", "*", "`date` BETWEEN '$debut' AND '$fin' AND `site`='$i' $reqSites AND absent='0' AND supprime='0'", "GROUP BY `date`,`debut`,`fin`");
            $lastDate=null;
            $lastEnd=null;
            if ($db->result) {
                foreach ($db->result as $elem) {
                    if ($elem['date']==$lastDate and $elem['debut']<$lastEnd) {
                        $totalHeures[$i]+=diff_heures($lastEnd, $elem['fin'], "decimal");
                    } else {
                        $totalHeures[$i]+=diff_heures($elem['debut'], $elem['fin'], "decimal");
                    }
                    $lastDate=$elem['date'];
                    $lastEnd=$elem['fin'];
                }
            }

            // Nombre de jours
            $totalJours[$i]=0;
            $db=new db();
            $db->select("pl_poste", "date", "`date` BETWEEN '$debut' AND '$fin' AND `site`='$i' $reqSites AND absent='0' AND supprime='0'", "GROUP BY `date`");
            $totalJours[$i]=$db->nb;

            // Nombre de semaines
            $totalSemaines[$i]=$totalJours[$i]>0?$totalJours[$i]/$joursParSemaine:1;
        }

        $echo="<p style='margin-top:0px;'>";
        for ($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++) {
            if ($GLOBALS['config']['Multisites-nombre']>1) {
                if (in_array($i, $selectedSites)) {
                    $echo.="<br/>{$GLOBALS['config']["Multisites-site$i"]}, ouverture au public : ";
                }
            } else {
                $echo.="<br/>Ouverture au public : ";
            }
            if (is_array($selectedSites) and in_array($i, $selectedSites)) {
                $echo.=heure4($totalHeures[$i]);
                $echo.=", {$totalJours[$i]} jours, ";
                $echo.=number_format($totalSemaines[$i], 1, ',', ' ')." semaines";
            }
        }
        $echo.="</p>\n";
        $this->ouvertureTexte=$echo;
    }
}
