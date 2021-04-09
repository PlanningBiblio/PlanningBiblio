<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : cron.ctrlPlanning.php
Création : 18 janvier 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Envoie un mail à la cellule planning pour l'informer de l'état des plannings à venir.
Nombre de jours ouvrés à contrôler paramétrable dans Administration / Configuration / Rappels
Les samedis et dimanches (si bibliothèque ouverte le dimanche) sont contrôlés en plus :
ex : 3 jours ouvrés à contrôler, le test du mercredi controlera le mercredi, jeudi, le vendredi, le samedi
ET le lundi suivant (3 jours ouvrés + samedi + jour courant)
Contrôle ou non des postes de renfort paramétrable dans Administration / Configuration / Rappels

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Controle du planning du lundi au vendredi à 7h
0 7 * * 1-5 /usr/bin/php -f /var/www/html/planning/src/Cron/Legacy/cron.ctrlPlannings.php
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
*/

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */

$version=$argv[0];

require_once(__DIR__ . '/../../../vendor/autoload.php');

use App\PlanningBiblio\Framework;

require_once(__DIR__ . '/../../../public/include/config.php');
require_once(__DIR__ . '/../../../public/include/function.php');
require_once(__DIR__ . '/../../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../../public/postes/class.postes.php');


// Génération d'un CSRF Token
session_start();
$CSRFToken = CSRFToken();

if (!$config['Rappels-Actifs']) {
    logs("Rappels désactivés", "Rappels", $CSRFToken);
    exit;
}

// Gestion des sites
$sites=array();
for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
    $sites[]=array($i,$config["Multisites-site".$i]);
}

// Dates à controler
$jours=$config['Rappels-Jours'];

// Recherche la date du jour et les $jours suivants
$dates=array();
for ($i=0;$i<=$jours;$i++) {
    $time=strtotime("+ $i days");
    $jour_semaine=date("w", $time);

    // Si le jour courant est un dimanche et que la bibliothèque n'ouvre pas les dimanches, on ne l'ajoute pas
    if ($jour_semaine!=0 or $config['Dimanche']) {
        $dates[]=date("Y-m-d", $time);
    }

    // Si le jour courant est un samedi, nous recherchons 2 jours supplémentaires pour avoir le bon nombre de jours ouvrés.
    // Nous controlons également le samedi et le dimanche
    if ($jour_semaine==6) {
        $jours=$jours+2;
    }
}

// Listes des postes
$p=new postes();
$p->fetch();
$postes=$p->elements;

// Création du message qui sera envoyé par e-mail
$data=array();

// Prépare la requête permettant de vérifier si les postes sont occupés
// On utilide PDO pour de meilleurs performances car la même requête sera executée de nombreuses fois avec des valeurs différentes
$dbh=new dbh();
$dbh->CSRFToken = $CSRFToken;
$dbh->prepare("SELECT `id`,`perso_id`,`absent` FROM `{$dbprefix}pl_poste` 
  WHERE `date`=:date AND `site`=:site AND `poste`=:poste AND `debut`=:debut AND `fin`=:fin AND `absent`='0' AND `supprime`='0';");


// Pour chaque date et pour chaque site
foreach ($dates as $date) {
    foreach ($sites as $site) {
    
    // on créé un tableau pour stocker les éléments par dates et sites
        $data[$date][$site[0]]=array("date"=>dateFr($date), "site"=>$site[1]);

        // On recherche les plannings qui ne sont pas créés (aucune structure affectée)
        $db=new db();
        $db->select2("pl_poste_tab_affect", null, array("date"=>$date, "site"=>$site[0]));
        if (!$db->result) {
            $data[$date][$site[0]]["message"]="Le planning {$site[1]} du <strong>".dateFr($date)." <span style='color:red;'>n'est pas cr&eacute;&eacute;</span></strong>\n";
            continue;
        } else {
            // Si le planning est créé, on récupère le numéro du tableau pour ensuite
            // comparer la structure au planning complété afin de trouver les cellules vides
            $tableauId=$db->result[0]['tableau'];

            // On recherche les plannings qui ne sont pas validés
            $db=new db();
            $db->select2("pl_poste_verrou", null, array("date"=>$date, "site"=>$site[0], "verrou2"=>1));
            if ($db->result) {
                $data[$date][$site[0]]["message"]="Le planning {$site[1]} du <strong>".dateFr($date)."</strong> est valid&eacute;\n";
            } else {
                $data[$date][$site[0]]["message"]="Le planning {$site[1]} du <strong>".dateFr($date)." <span style='color:red;'>n'est pas valid&eacute;</span></strong>\n";
            }
        }

        // On recherche les plannings qui ne sont pas complets (cellules vides)
        // Recherche des tableaux (structures)
        $t = new Framework();
        $t->id=$tableauId;
        $t->get();
        $tableau=$t->elements;
    
        foreach ($tableau as $elem) {
    
      // On stock dans notre tableau data les éléments date, site, tableau
            $data[$date][$site[0]]['tableau'][$elem['nom']]["tableau"]=$elem['titre'];

            // $tab = liste des postes/plages horaires non occupés, cellules grisées excluses, poste non obligatoires exclus selon config
            $tab=array();
            $i=-1;
      
            // Pour chaque ligne du tableau (structure)
            foreach ($elem['lignes'] as $l) {
                // Ne regarde que les lignes "postes"
                if ($l['type']=="poste") {
                    // Pour chaque créneau horaire du tableau (structure)
                    foreach ($elem['horaires'] as $key => $h) {
                        // Si cellule grisées, on l'exclus (donc continue)
                        if (in_array($l['ligne']."_".($key+1), $elem['cellules_grises'])) {
                            continue;
                        }
                        // Si on ne veut pas des postes de renfort et si le poste n'est pas obligatoire, on l'exclus
                        if (!$config['Rappels-Renfort'] and $postes[$l['poste']]['obligatoire']!="Obligatoire") {
                            continue;
                        }

                        // On contrôle si le poste est occupé
                        // Pour ceci, on execute la requête préparée plus haut avec PDO
                        $sql=array(":date"=>$date, ":site"=>$site[0], ":poste"=>$l['poste'], ":debut"=>$h['debut'], ":fin"=>$h['fin']);
                        $dbh->result=array();
                        $dbh->execute($sql);
                        $result=$dbh->result;

                        // Contrôle des absences et des congés
                        // Si la dernière execution de la requête donne un résultat
                        // Vérifier qu'au moins un des agents issus de ce résultat n'est pas absent
                        $tousAbsents=true;
                        if (!empty($result)) {
                            foreach ($result as $res) {
                                // Contrôle des absences
                                $absent=false;
                                $a=new absences();
                                if ($a->check($res['perso_id'], $date." ".$h['debut'], $date." ".$h['fin'])) {
                                    $absent=true;
                                }
        
                                // Contrôle des congés
                                $conges=false;
                                if ($config['Conges-Enable']) {
                                    require_once(__DIR__ . '/../../../public/conges/class.conges.php');
                                    $c=new conges();
                                    if ($c->check($res['perso_id'], $date." ".$h['debut'], $date." ".$h['fin'])) {
                                        $conges=true;
                                    }
                                }
        
                                // Si l'agent n'est ni absent, ni en congés : on a une présence
                                if (!$absent and !$conges) {
                                    $tousAbsents=false;
                                    break;
                                }
                            }
                        }

                        // Si la dernière execution de la requête ne donne pas de résultat ou que tous les agents issus du résultat sont absents
                        if (empty($result) or $tousAbsents) {
                            // On enregistre dans le table les informations de la cellule

                            // On regroupe les horaires qui se suivent sur un même poste
                            if (!empty($tab) and $tab[$i]['fin']==$h['debut'] and $tab[$i]['poste_id']==$l['poste']) {
                                $tab[$i]["fin"]=$h['fin'];
                            } else {
                                $i++;
                                $tab[$i]=array("poste"=>$postes[$l['poste']]['nom'], "poste_id"=>$l['poste'], "debut"=>$h['debut'], "fin"=>$h['fin']);
                            }
                        }
                    }
                }
            }
            $data[$date][$site[0]]['tableau'][$elem['nom']]["data"]=$tab;
        }
    }
}

// Création du message
$msg="Voici l&apos;&eacute;tat des plannings du ".dateFr($dates[0])." au ".dateFr($dates[count($dates)-1]);
$msg.="<ul>\n";
foreach ($data as $date) {
    foreach ($date as $site) {
        $msg.="<li style='margin-bottom:15px;'>\n";
        if (array_key_exists("message", $site)) {
            $msg.=$site['message'];
        }
        if (array_key_exists("tableau", $site)) {
            $msg.="<br/>\nLes postes suivants ne sont pas occup&eacute;s :\n<ul>\n";
            foreach ($site['tableau'] as $tableau) {
                $msg.="<li>Tableau <strong>{$tableau['tableau']}</strong> :\n<ul>\n";
                foreach ($tableau['data'] as $poste) {
                    $msg.="<li>{$poste['poste']}, de ".heure2($poste['debut'])." &agrave; ".heure2($poste['fin'])."</li>\n";
                }
                $msg.="</ul>\n";
            }
            $msg.="</ul>\n";
        }
        $msg.="</li>\n";
    }
}
$msg.="</ul>\n";

$subject="Plannings du ".dateFr($dates[0])." au ".dateFr($dates[count($dates)-1]);
$to=explode(";", $config['Mail-Planning']);

$m=new CJMail();
$m->to=$to;
$m->subject=$subject;
$m->message=$msg;
$m->send();
if ($m->error) {
    logs($m->error, "Rappels", $CSRFToken);
}
