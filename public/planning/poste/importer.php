<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/planning/poste/importer.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'importer un modèle de planning.
Affiche les modèles disponibles, copie le tableau du modèle choisi et ses données dans la base de données

Cette page est appelée par la fonction JavaScript Popup qui l'affiche dans un cadre flottant
*/

require_once "class.planning.php";

use App\Model\Agent;
use App\Model\Model;

// Initialisation des variables
$CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);
$get_absents=filter_input(INPUT_GET, "absents", FILTER_SANITIZE_STRING);
$model_id = filter_input(INPUT_GET, "model", FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);

// Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
$date=filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$get_absents=filter_var($get_absents, FILTER_CALLBACK, array("options"=>"sanitize_on"));

$attention="<span style='color:red;'>Attention, le planning actuel sera remplacé par le modèle<br/><br/></span>\n";

// Sécurité
// Refuser l'accès aux agents n'ayant pas les droits de modifier le planning
if (!in_array((300+$site), $droits)) {
    echo "<div id='acces_refuse'>Accès refusé</div>";
    echo "<a href='javascript:popup_closed();'>Fermer</a>\n";
    exit;
}

echo <<<EOD
  <div style='text-align:center'>
  <b>Importation d'un modèle</b>
  <br/><br/>
EOD;
$entityManager = $GLOBALS['entityManager'];
if (!$model_id) {		// Etape 1 : Choix du modèle à importer
    $semaine = " ";

    $queryBuilder = $entityManager->createQueryBuilder();

    $models = $queryBuilder->select(array('m'))
    ->from(Model::class, 'm')
    ->where('m.site = :site')
    ->setParameter('site', $site)
    ->groupBy('m.nom')
    ->getQuery()
    ->getResult();


    // No model yet.
    if (!$models) {
        echo "Aucun modèle enregistré<br/><br/><a href='javascript:popup_closed();'>Fermer</a>\n";
    }
    // Only one model.
    elseif (count($models) == 1) {
        echo $attention;
        $model = $models[0];
        if ($model->isWeek()) {
            $semaine = "(semaine) ";
        }
        echo "<form name='form' method='get' action='index.php' onsubmit='return ctrl_form(\"nom\");'>\n";
        echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
        echo "<input type='hidden' name='page' value='planning/poste/importer.php' />\n";
        echo "<input type='hidden' name='menu' value='off' />\n";
        echo "<input type='hidden' name='model' value='{$model->model_id()}' />\n";
        echo "<input type='hidden' name='date' value='$date' />\n";
        echo "<input type='hidden' name='site' value='$site' />\n";
        echo "Importer le modèle \"{$model->nom()}\" $semaine?<br/><br/>\n";
        echo "Importer les absents ?&nbsp;&nbsp;";
        echo "<input type='checkbox' name='absents' checked='checked' /><br/><br/>\n";
        echo "<a href='#' onclick='document.form.submit();'>Oui</a>";
        echo "&nbsp;&nbsp;\n";
        echo "<a href='javascript:popup_closed();'>Non</a>\n";
        echo "</form>\n";
    }
    // Many models (dropdown list).
    else {
        echo $attention;
        echo "Sélectionnez le modèle à importer<br/><br/>\n";
        echo "<form name='form' method='get' action='index.php' onsubmit='return ctrl_form(\"nom\");'>\n";
        echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
        echo "<input type='hidden' name='page' value='planning/poste/importer.php' />\n";
        echo "<input type='hidden' name='menu' value='off' />\n";
        echo "<input type='hidden' name='date' value='$date' />\n";
        echo "<input type='hidden' name='site' value='$site' />\n";
        echo "<select name='model' id='model'>\n";
        echo "<option value=''>&nbsp;</option>\n";
        foreach ($models as $model) {
            $semaine = " ";
            if ($model->isWeek()) {
                $semaine = " (semaine)";
            }
            echo "<option value='{$model->model_id()}'>{$model->nom()} $semaine</option>\n";
        }
        echo "</select><br/>\n";
        echo "Importer les absents ?&nbsp;&nbsp;";
        echo "<input type='checkbox' name='absents' checked='checked' /><br/><br/>\n";
        echo "<input type='button' value='Annuler' onclick='popup_closed();' />\n";
        echo "&nbsp;&nbsp;\n";
        echo "<input type='submit' value='Valider'/>\n";
        echo "</form>\n";
    }
} else {					// Etape 2 : Insertion des données

    $model = $entityManager
        ->getRepository(Model::Class)
        ->findOneBy(array('model_id' => $model_id));

    $dates=array();
    $d=new datePl($date);

    if ($model->isWeek()) {
        foreach ($d->dates as $elem) {	// Recherche de toute les dates de la semaine en cours pour insérer les données
            $dates[]=$elem;
        }
    } else {
        $dates[0]=$date;			// S'il ne s'agit pas d'un modèle semaine, insertion seulement pour le jour en cours
    }

    // Recherche des agents placés sur d'autres sites
    $autres_sites = array();
    if ($config['Multisites-nombre']>1) {
        $db = new db();
        $db->select2('pl_poste', array('perso_id','date','debut','fin'), array('date' => "BETWEEN {$dates[0]} AND ".end($dates), 'site' => "<>$site"));
        if ($db->result) {
            foreach ($db->result as $as) {
                $autres_sites[$as['perso_id'].'_'.$as['date']][] = array('debut' => $as['debut'], 'fin' => $as['fin']);
            }
        }
    }

    // Find all agents that are not deleted
    $agents = $entityManager->getRepository('App\Model\Agent')->findBy(array('supprime' =>'0'));
    if (!empty($agents)) {
        foreach ($agents as $agent) {
            $agent_list[] = $agent->id();
        }
    }

    // if module PlanningHebdo: search related plannings.
    if ($config['PlanningHebdo']) {
        include(__DIR__.'/../../planningHebdo/planning.php');
    }

    $i=0;
    foreach ($dates as $elem) {
        $i++;				// utilisé pour la colone jour du modèle (1=lundi, 2=mardi ...) : on commence à 1
        $sql=null;
        $values=array();
        $absents=array();

        $db = new db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste_tab_affect", array("date"=>$elem, "site"=>$site));

        // Importation du tableau
        // S'il s'agit d'un modèle pour une semaine
        if ($model->isWeek()) {
            $db=new db();
            $db->select2("pl_poste_modeles_tab", "*", array("model_id"=>$model_id, "site"=>$site, "jour"=>$i));
        // S'il s'agit d'un modèle pour un seul jour
        } else {
            $db=new db();
            $db->select2("pl_poste_modeles_tab", "*", array("model_id"=>$model_id, "site"=>$site));
        }

        if ($db->result) {
            $tableau=$db->result[0]['tableau'];
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("pl_poste_tab_affect", array("date"=>$elem ,"tableau"=>$tableau ,"site"=>$site ));

            // N'importe pas les agents placés sur des postes supprimés (si tableau modifié)
            $postes = array();
            $db = new db();
            $db->select2('pl_poste_lignes', 'poste', array('type'=>'poste', 'numero'=>$tableau));
            if ($db->result) {
                foreach ($db->result as $elem2) {
                    $postes[] = $elem2['poste'];
                }
            }

            // N'importe pas les agents placés sur des horaires supprimés (si tableau modifié)
            $horaires = array();
            $db = new db();
            $db->select2('pl_poste_horaires', array('debut','fin'), array('numero'=>$tableau));
            if ($db->result) {
                foreach ($db->result as $elem2) {
                    $horaires[] = array('debut'=>$elem2['debut'], 'fin'=>$elem2['fin']);
                }
            }
        }

        // Importation des agents
        // S'il s'agit d'un modèle pour une semaine
        if ($model->isWeek()) {
            $db=new db();
            $db->select2("pl_poste_modeles", "*", array("model_id" => $model_id, "site"=>$site, "jour"=>$i));
        // S'il s'agit d'un modèle pour un seul jour
        } else {
            $db=new db();
            $db->select2("pl_poste_modeles", "*", array("model_id" => $model_id, "site"=>$site));
        }

    
    
        $filter=$config['Absences-validation']?"AND `valide`>0":null;
        if ($db->result) {
            foreach ($db->result as $elem2) {

                // Don't import deleted agents
                if ($elem2['perso_id'] > 0 and !in_array($elem2['perso_id'], $agent_list)) {
                    continue;
                }

                $value = array();

                // On n'importe pas les agents s'ils sont placés sur un autre site
                if (isset($autres_sites[$elem2['perso_id'].'_'.$elem])) {
                    foreach ($autres_sites[$elem2['perso_id'].'_'.$elem] as $as) {
                        if ($as['debut'] < $elem2['fin'] and $as['fin'] > $elem2['debut']) {
                            continue 2;
                        }
                    }
                }

                $grise = $elem2['perso_id'] == 0 ? 1 : 0;

                $value = array(
                    ':date' => $elem,
                    ':perso_id' => $elem2['perso_id'],
                    ':poste' => $elem2['poste'],
                    ':debut' => $elem2['debut'],
                    ':fin' => $elem2['fin'],
                    ':site' => $site,
                    ':absent' => 0,
                    ':grise' => $grise
                );


                $debut=$elem." ".$elem2['debut'];
                $fin=$elem." ".$elem2['fin'];

                // Look for absences
                $db2 = new db();
                $db2->select("absences", "*", "`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' $filter ");
                $absent = $db2->result ? true : false;

                // Look for hollidays
                $db2 = new db();
                $db2->select("conges", "*", "`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' AND `valide`>0");
                $absent = $db2->result ? true : $absent ;

                // Don't import if absent and get_absents not checked
                if (!$get_absents and $absent) {
                    continue;
                }

                // Check if the agent is out of his schedule (schedule has been changed).
                $week_number = 0;

                if ($config['PlanningHebdo']) {
                    $temps = !empty($tempsPlanningHebdo[$elem2['perso_id']]['temps']) ? $tempsPlanningHebdo[$elem2['perso_id']]['temps'] : array();
                    $week_number = !empty($tempsPlanningHebdo[$elem2['perso_id']]['nb_semaine']) ? $tempsPlanningHebdo[$elem2['perso_id']]['nb_semaine'] : 0 ;
                } else {
                    $agent = $entityManager->find(Agent::class, $elem2['perso_id']);
                    if (!empty($agent)) {
                        $temps = json_decode(html_entity_decode($agent->temps(), ENT_QUOTES, 'UTF-8'), true);
                    } else {
                        $temps = array();
                    }
                }

                $d = new datePl($elem);
                $day_index = $d->planning_day_index_for($elem2['perso_id'], $week_number);
                if (!calculSiPresent($elem2['debut'], $elem2['fin'], $temps, $day_index)) {
                    $value[':absent'] = 2;
                }

                if (isset($value[':absent'])) {
                    $values[] = $value;
                }
            }

            // insertion des données dans le planning du jour
            if (!empty($values)) {
                // Suppression des anciennes données
                $db=new db();
                $db->CSRFToken = $CSRFToken;
                $db->delete("pl_poste", array("date"=>$elem, "site"=>$site));

                // Insertion des nouvelles données
                $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`perso_id`,`poste`,`debut`,`fin`,`absent`,`site`,`grise`) ";
                $req.="VALUES (:date, :perso_id, :poste, :debut, :fin, :absent, :site, :grise);";
                $dbh=new dbh();
                $dbh->CSRFToken = $CSRFToken;
                $dbh->prepare($req);
                foreach ($values as $value) {
                    $dbh->execute($value);
                }
            }
        }
    }
    echo "<script type='text/JavaScript'>top.document.location.href=\"index.php?date=$date\";</script>\n";
}
