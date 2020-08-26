<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/planningHebdo/modif.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des plannings de présence pour l'administrateur
Page accessible à partir du menu administration / Heures de présence
*/

require_once "class.planningHebdo.php";

$twig = $GLOBALS['twig'];
$request = $GLOBALS['request'];

// Initialisation des variables
$copy = $request->get('copy');
$request_exception = $request->get('exception');
$id = $request->get('id');
$retour = $request->get('retour');
$is_exception = 0;
$exception_id = '';

$exception_back = 'monCompte.php';
if ($retour != 'monCompte.php') {
    $exception_back = $retour;
    $retour = "planningHebdo/$retour";
}

if ($copy) {
    $id=$copy;
}

if ($request_exception) {
    $id = $request_exception;
}

$is_new = 0;
if (!$id) {
    $is_new = 1;
}

// Sécurité
$adminN1 = in_array(1101, $droits);
$adminN2 = in_array(1201, $droits);

$cle = null;

if ($id) {
    $p=new planningHebdo();
    $p->id=$id;
    $p->fetch();
    if (empty($p->elements)) {
        echo "<h3>Heures de présence</h3>\n";
        echo "<p>Les heures demandées ne sont plus accessibles à cette adresse.<br/>\n";
        echo "Veuillez les rechercher dans le menu menu <a href='index.php?page=planningHebdo/index.php'>Administration / Heures de présence</a></p>\n";
        include "include/footer.php";
        exit;
    }
    $debut1=$p->elements[0]['debut'];
    $fin1=$p->elements[0]['fin'];
    $debut1Fr=dateFr($debut1);
    $fin1Fr=dateFr($fin1);

    $perso_id=$p->elements[0]['perso_id'];
    $temps=$p->elements[0]['temps'];
    $breaktime=$p->elements[0]['breaktime'];

    if ($p->elements[0]['exception']) {
        $is_exception = 1;
        $exception_id = $p->elements[0]['exception'];
    }

    if ($copy or $request_exception) {
        $valide_n1 = 0;
        $valide_n2 = 0;
    } else {
        $valide_n1 = $p->elements[0]['valide_n1'] ?? 0;
        $valide_n2 = $p->elements[0]['valide'] ?? 0;
    }

    $remplace=$p->elements[0]['remplace'];
    $cle=$p->elements[0]['cle'];

    // Informations sur l'agents
    $p=new personnel();
    $p->fetchById($perso_id);
    $sites=$p->elements[0]['sites'];

    // Droits de gestion des plannings de présence agent par agent
    if ($adminN1 and $config['PlanningHebdo-notifications-agent-par-agent']) {
        $db = new db();
        $db->select2('responsables', 'perso_id', array('perso_id' => $perso_id, 'responsable' => $_SESSION['login_id']));
    
        $adminN1 = $db->result ? true : false;
    }

    // Modif autorisée si n'est pas validé ou si validé avec des périodes non définies (BSB).
    // Dans le 2eme cas copie des heures de présence avec modification des dates
    $action="modif";
    $modifAutorisee=true;

    if (!($adminN1 or $adminN2) and !$config['PlanningHebdo-Agents']) {
        $modifAutorisee=false;
    }
  
    // Si le champ clé est renseigné, les heures de présences ont été importées automatiquement depuis une source externe. Donc pas de modif
    if ($cle) {
        $modifAutorisee = false;
    }

    if (!($adminN1 or $adminN2) and $valide_n2 > 0) {
        $action="copie";
    }

    if ($copy or $request_exception) {
        $action="ajout";
    }
} else {
    $action="ajout";
    $modifAutorisee=true;
    $debut1=null;
    $fin1=null;
    $debut1Fr=null;
    $fin1Fr=null;
    $perso_id=$_SESSION['login_id'];
    $temps=null;
    $valide_n2 = 0;
    $remplace=null;
    $sites=array();
    for ($i=1;$i<$config['Multisites-nombre']+1;$i++) {
        $sites[]=$i;
    }
    $valide_n1 = 0;
    $valide_n2 = 0;

}

// Sécurité
if (!($adminN1 or $adminN2) and $id and $perso_id!=$_SESSION['login_id']) {
    echo "<div id='acces_refuse'>Accès refusé</div>\n";
    include "include/footer.php";
    exit;
}

$nomAgent = nom($perso_id, "prenom nom");

?>


<!-- Formulaire Heures de présence-->
<h3>Heures de présence</h3>
<?php
if ($id and !$copy and !$request_exception) {
    echo "<h3>Heures de $nomAgent du $debut1Fr au $fin1Fr</h3>";
}
?>
<div id='working_hours'>
<?php
echo "<form name='form1' method='post' action='index.php' onsubmit='return plHebdoVerifForm();'>\n";

// Modification
if ($id and !$copy and !$request_exception) {
    echo "<input type='hidden' name='perso_id' value='$perso_id' id='perso_id' />\n";
// Ajout ou copie
} else {
    if ($request_exception) {
        echo "<input type='hidden' name='perso_id' value='$perso_id' id='perso_id' />\n";
    }

    if ($config['PlanningHebdo-notifications-agent-par-agent'] and !$adminN2) {
        // Sélection des agents gérés (table responsables) et de l'agent logué

        $perso_ids = array($_SESSION['login_id']);
        $db = new db();
        $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
        if ($db->result) {
            foreach ($db->result as $elem) {
                $perso_ids[] = $elem['perso_id'];
            }
        }

        $perso_ids = implode(',', $perso_ids);
        $db=new db();
        $db->select2('personnel', null, array('supprime'=>0, 'id' => "IN$perso_ids"), 'order by nom,prenom');
    } else {
        $db=new db();
        $db->select2('personnel', null, array('supprime'=>0), 'order by nom,prenom');
    }

    // Non admin
    if (!($adminN1 or $adminN2)) {
        echo "<h3>Nouveaux horaires pour $nomAgent</h3>\n";
    }
    // Copie
    elseif ($copy) {
        echo "<h3>Copie des heures de $nomAgent du $debut1Fr au $fin1Fr</h3>\n";
    // Ajout par un admin
    }
    elseif ($request_exception) {
        echo "<h3>Création d'une exception au planning de $nomAgent du $debut1Fr au $fin1Fr</h3>\n";
    } else {
        echo "<h3>Nouveaux horaires</h3>\n";
    }
    echo "<div id='plHebdo-perso-id'>\n";
    if (($adminN1 or $adminN2) and !$request_exception) {
        echo "<label for='perso_id'>Pour l'agent</label>\n";
        echo "<select name='perso_id' class='ui-widget-content ui-corner-all' id='perso_id' style='position:absolute; left:200px; width:200px; text-align:center;' >\n";
        echo "<option value=''>&nbsp;</option>\n";
        foreach ($db->result as $elem) {
            $selected=$perso_id==$elem['id']?"selected='selected'":null;
            echo "<option value='{$elem['id']}' $selected >{$elem['nom']} {$elem['prenom']}</option>\n";
        }
        echo "</select>\n";
    } else {
        echo "<input type='hidden' name='perso_id' value='$perso_id' id='perso_id' />\n";
    }
    echo "</div>\n";
}

// Choix de la période d'utilisation et validation
if ($request_exception) {
    $debut1Fr = '';
    $fin1Fr = '';
}
echo "<div id='periode'>\n";
echo <<<EOD
  <p><label for='debut'>Début d'utilisation</label>
  <input type='text' name='debut' value='$debut1Fr' class='datepicker' style='position:absolute; left:200px; width:200px;' /></p>
  <p><label for='fin'>Fin d'utilisation</label>
  <input type='text' name='fin' value='$fin1Fr' class='datepicker' style='position:absolute; left:200px; width:200px;' /></p>
EOD;

echo "</div> <!-- id=periode -->\n";

if ($request_exception) {
    $exception_id = $id;
    $id = '';
}

if ($copy) {
    $id = '';
}

?>
<input type='hidden' name='page' value='planningHebdo/valid.php' />
<input type='hidden' name='CSRFToken' value='<?php echo $CSRFSession; ?>' />
<input type='hidden' name='action' value='<?php echo $action; ?>' />
<input type='hidden' name='retour' value='<?php echo $retour; ?>' />
<input type='hidden' name='id' value='<?php echo $id; ?>' />
<input type='hidden' name='valide' value='<?php echo $_SESSION['login_id']; ?>' />
<input type='hidden' name='remplace' value='<?php echo $remplace; ?>' />
<input type='hidden' name='exception' value='<?php echo $exception_id; ?>' />

<!-- Affichage des tableaux avec la sélection des horaires -->
<?php
switch ($config['nb_semaine']) {
  case 2: $cellule=array("Semaine Impaire","Semaine Paire");		break;
  case 3: $cellule=array("Semaine 1","Semaine 2","Semaine 3");		break;
  default: $cellule=array("Jour");					break;
}
$fin=$config['Dimanche']?array(8,15,22):array(7,14,21);
$debut=array(1,8,15);
$jours=array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");

?>

<?php
for ($j=0;$j<$config['nb_semaine'];$j++) {
    echo "<h3>{$cellule[$j]}</h3>\n";

    // Affichage de la case à cocher "Mêmes heures qu'en semaine 1"
    if ($j>0) {
        if ($modifAutorisee) {
            echo "<p><input type='checkbox' name='memePlanning$j' class='memePlanning' data-id='$j' id='memePlanning$j' />";
            echo "<label for='memePlanning$j' >Mêmes heures qu'en {$cellule[0]}</label></p>\n";
        } else {
            echo "<p style='display:none;' id='memePlanning$j' ><strong>Mêmes heures qu'en {$cellule[0]}</strong></p>\n";
        }
    }

    echo "<div id='div$j'>\n";
    echo "<table border='1' cellspacing='0' id='tableau{$j}' class='tableau' data-id='$j' >\n";
    echo "<tr style='text-align:center;'><td style='width:135px;'>{$cellule[$j]}</td><td style='width:135px;'>Heure d'arrivée</td>";
  
    if ($config['PlanningHebdo-Pause2']) {
        echo "<td style='width:135px;'>Début de pause 1</td><td style='width:135px;'>Fin de pause 1</td>";
        echo "<td style='width:135px;'>Début de pause 2</td><td style='width:135px;'>Fin de pause 2</td>";
    } else {
        echo "<td style='width:135px;'>Début de pause</td><td style='width:135px;'>Fin de pause</td>";
    }
  
    echo "<td style='width:135px;'>Heure de départ</td>";
    if ($config['PlanningHebdo-PauseLibre']) {
        echo "<td style='width:135px;'>Temps de pause</td>";
    }
    if ($config['Multisites-nombre']>1) {
        echo "<td style='width:135px;'>Site</td>";
    }
    echo "<td style='width:135px;'>Temps</td>";
    echo "</tr>\n";
    for ($i=$debut[$j];$i<$fin[$j];$i++) {
        $k=$i-($j*7)-1;

        $breaktime[$i - 1] = isset($breaktime[$i - 1]) ? $breaktime[$i - 1] : 0;

        echo "<tr style='text-align:center;'><td>{$jours[$k]}</td>";
        if ($modifAutorisee) {
            echo "<td>".selectTemps($i-1, 0, null, "select")."</td>";
            echo "<td>".selectTemps($i-1, 1, null, "select")."</td>";
            echo "<td>".selectTemps($i-1, 2, null, "select")."</td>";
            if ($config['PlanningHebdo-Pause2']) {
                echo "<td>".selectTemps($i-1, 5, null, "select")."</td>\n";
                echo "<td>".selectTemps($i-1, 6, null, "select")."</td>\n";
            }
            echo "<td>".selectTemps($i-1, 3, null, "select")."</td>";
            if ($config['PlanningHebdo-PauseLibre']) {
                echo "<td>";
                echo $twig->render('weeklyplanning/breakingtime.html.twig',
                    array('breaktime' => $breaktime[$i - 1], 'day_index' => $i - 1));
                echo "</td>";
            }
        } else {
            $h1 = isset($temps[$i-1])?heure2($temps[$i-1][0]):null;
            $h2 = isset($temps[$i-1])?heure2($temps[$i-1][1]):null;
            $h3 = isset($temps[$i-1])?heure2($temps[$i-1][2]):null;
            if ($config['PlanningHebdo-Pause2']) {
                $h5 = isset($temps[$i-1])?heure2($temps[$i-1][5]):null;
                $h6 = isset($temps[$i-1])?heure2($temps[$i-1][6]):null;
            }
            $h4 = isset($temps[$i-1])?heure2($temps[$i-1][3]):null;

            echo "<td id='temps_".($i-1)."_0' class='td_heures'>$h1</td>\n";
            echo "<td id='temps_".($i-1)."_1' class='td_heures'>$h2</td>\n";
            echo "<td id='temps_".($i-1)."_2' class='td_heures'>$h3</td>\n";
            if ($config['PlanningHebdo-Pause2']) {
                echo "<td id='temps_".($i-1)."_5' class='td_heures'>$h5</td>\n";
                echo "<td id='temps_".($i-1)."_6' class='td_heures'>$h6</td>\n";
            }
            echo "<td id='temps_".($i-1)."_3' class='td_heures'>$h4</td>\n";
            if ($config['PlanningHebdo-PauseLibre']) {
                echo "<td id='breaktime_" . ($i -1) ."'>";
                echo heure4($breaktime[$i -1]);
                echo "<input type='hidden' name='breaktime_" . ($i -1) . "' value='" . $breaktime[$i -1] . "'/>";
                echo "</td>";
            }
        }
        if ($config['Multisites-nombre']>1) {
            if ($modifAutorisee) {
                echo "<td><select name='temps[".($i-1)."][4]' class='select selectSite'>\n";
                if (count($sites)>1) {
                    echo "<option value=''>&nbsp;</option>\n";
                }
                foreach ($sites as $site) {
                    $selected = isset($temps) && $temps[$i-1][4] == $site ? "selected='selected'" : null;
                    echo "<option value='$site' $selected >{$config["Multisites-site{$site}"]}</option>\n";
                }
                echo "</select></td>";
            } else {
                $site = isset($temps[$i-1][4]) ? $temps[$i-1][4] : null;
                $site=$site?$config["Multisites-site{$site}"]:"&nbsp;";
                echo "<td class='td_heures'>$site</td>\n";
            }
        }
        echo "<td id='heures_{$j}_$i'></td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "Nombre d'heures : <font id='heures_{$j}' style='font-weight:bold;'>&nbsp;</font><br/>\n";
    echo "</div>\n";
}

echo "<div id='informations' style='margin-top:30px;' >\n";

// Si le champ clé est renseigné, les heures de présences ont été importées automatiquement depuis une source externe. Donc pas de modif
if ($cle) {
    echo "<p><b class='important'>Les horaires ont été importés depuis une source externe.</b></p>\n";
} elseif (!$modifAutorisee) {
    echo "<p><b class='important'>Vos horaires ont été validés.</b><br/>Pour les modifier, contactez votre chef de service.</p>\n";
} elseif ($valide_n2 > 0 and !($adminN1 or $adminN2)) {
    echo "<p><b class='important'>Vos horaires ont été validés.</b><br/>Si vous souhaitez les changer, modifiez la date de début et/ou de fin d'effet.<br/>";
    echo "Vos nouveaux horaires seront enregistrés et devront être validés par un administrateur.<br/>";
    echo "Les anciens horaires seront conservés en attendant la validation des nouveaux.</p>\n";
} elseif ($valide_n2 > 0 and ($adminN1 or $adminN2) and !$copy) {
    echo "<p style='width:850px;text-align:justify;margin-top:30px;'><b class='important'>Ces horaires ont été validés.</b><br/>";
    echo "Leur modification aura un effet immédiat.</p>\n";
    //   echo "En tant qu'administrateur, vous pouvez les modifier et les enregistrer en tant que copie.<br/>";
//   echo "Dans ce cas, modifiez la date de début et/ou de fin d'effet. ";
//   echo "Les nouveaux horaires seront enregistrés et devront ensuite être validés. ";
//   echo "Les anciens horaires seront conservés en attendant la validation des nouveaux.<br/>";
//   echo "Vous pouvez également les enregistrer directement mais dans ce cas, vous ne conserverez pas les anciens horaires.</p>\n";
}

if (($copy or $request_exception) and $config['Multisites-nombre']>1) {
    echo <<<EOD
  <p id='info_copie' style='display:none;' class='important'><strong>
    Attention : Veuillez vérifier les affectations aux sites avant d'enregistrer.
  </strong></p>
EOD;
}
echo "</div> <!-- id=informations -->\n";

// Validation
if (!$cle) {
    // Si admin, affiche le menu déroulant
    if ($adminN1 or $adminN2) {
        $selected1 = isset($valide_n1) && $valide_n1 > 0 ? "selected='selected'" : null;
        $selected2 = isset($valide_n1) && $valide_n1 < 0 ? "selected='selected'" : null;
        $selected3 = isset($valide_n2) && $valide_n2 > 0 ? "selected='selected'" : null;
        $selected4 = isset($valide_n2) && $valide_n2 < 0 ? "selected='selected'" : null;

        echo "<p><label for='validation'>Validation</label>\n";
        echo "<select name='validation' id='validation' style='position:absolute; left:200px; width:200px;' >\n";
        if ($adminN1 or $valide_n1 == 0) {
            echo "<option value='0'>Demand&eacute;</option>\n";
        }
        if ($adminN1 or ($valide_n1 > 0 and $valide_n2 == 0)) {
            echo "<option value='1' $selected1 >{$lang['work_hours_dropdown_accepted_pending']}</option>\n";
        }
        if ($adminN1 or ($valide_n1 < 0 and $valide_n2 == 0)) {
            echo "<option value='-1' $selected2 >{$lang['work_hours_dropdown_refused_pending']}</option>\n";
        }
        if (($adminN2 and ($valide_n1 > 0 or $config['PlanningHebdo-Validation-N2'] == 0)) or $valide_n2 > 0) {
            echo "<option value='2' $selected3 >{$lang['work_hours_dropdown_accepted']}</option>\n";
        }
        if (($adminN2 and ($valide_n1 > 0 or $config['PlanningHebdo-Validation-N2'] == 0)) or $valide_n2 < 0) {
            echo "<option value='-2' $selected4 >{$lang['work_hours_dropdown_refused']}</option>\n";
        }
        echo "</select></p>\n";

    // Si pas admin, affiche le niveau en validation en texte simple
    } else {
        $validation = "Demandé";
        if ($valide_n2 > 0) {
            $validation = $lang['work_hours_dropdown_accepted'];
        } elseif ($valide_n2 < 0) {
            $validation = $lang['work_hours_dropdown_refused'];
        } elseif ($valide_n1 > 0) {
            $validation = $lang['work_hours_dropdown_accepted_pending'];
        } elseif ($valide_n1 < 0) {
            $validation = $lang['work_hours_dropdown_refused_pending'];
        }

        echo "<p><label>Validation</label>\n";
        echo "<span style='position:absolute; left:200px;'>$validation</span>\n";
        echo "</p>\n";
    }
}

echo "<div id='boutons' style='padding-top:50px;'>\n";
echo "<input type='button' value='Retour' onclick='location.href=\"index.php?page=$retour\";' class='ui-button' />\n";

// Si le champ clé est renseigné, les heures de présences ont été importées automatiquement depuis une source externe. Donc pas de modif
if (($adminN1 or $adminN2) and !$cle) {
    if ($request_exception) {
        echo '<input id="save-exception" type="submit" value="Enregistrer l\'exception" style="margin-left:30px;" class="ui-button" />';
        echo "\n";
    } else {
        echo "<input type='submit' value='Enregistrer' style='margin-left:30px;' class='ui-button' />\n";
    }
//   if($valide_n2 > 0 and !$copy){
//     echo "<input type='button' value='Enregistrer une copie' style='margin-left:30px;' onclick='$(\"input[name=action]\").val(\"copie\");$(\"form[name=form1]\").submit();' class='ui-button' />\n";
//   }
} elseif ($modifAutorisee) {
    if ($request_exception) {
        echo '<input id="save-exception" type="submit" value="Enregistrer l\'exception" style="margin-left:30px;" class="ui-button" />';
        echo "\n";
    } else {
        echo "<input type='submit' value='Enregistrer' style='margin-left:30px;' class='ui-button' />\n";
    }
}

if (($adminN1 or $adminN2 or $modifAutorisee) and (!$request_exception and !$is_exception and !$copy and !$is_new)) {
    echo "<input type='button' value='Ajouter une exception' onclick='location.href=\"index.php?page=planningHebdo/modif.php&exception=$id&retour=$exception_back\";' style='margin-left:30px;' class='ui-button' />\n";
}

?>
</div> <!-- id=boutons -->

</form>
<script type='text/JavaScript'>
$("document").ready(function(){
  plHebdoCalculHeures2();
  plHebdoMemePlanning();
});
$(".select").change(function(){
  plHebdoCalculHeures($(this),"");
  plHebdoChangeHiddenSelect();
});
$("#perso_id").change(function(){
  $("#info_copie").show();
});
</script>

</div> <!-- working_hours -->