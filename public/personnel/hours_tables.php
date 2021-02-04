<?php
use App\PlanningBiblio\Helper\HolidayHelper;

# Very very tricky solution but this is this fatsest
# way to twigizing this part.
$config = $GLOBALS['config'];
$temps = $GLOBALS['temps'];
$hours_tab = '';

if ($config['PlanningHebdo']) {
    $holiday_helper = new HolidayHelper(array("perso_id" => $id));
    $result = $holiday_helper->getPlanning();
    $nb_semaine = $result['nb_semaine'] ?? $config['nb_semaine'];
} else {
    $nb_semaine = $config['nb_semaine'];
}

switch ($nb_semaine) {
  case 2: $cellule=array("Semaine Impaire","Semaine Paire");		break;
  case 3: $cellule=array("Semaine 1","Semaine 2","Semaine 3");		break;
  default: $cellule=array("Jour");					break;
}
$fin = $config['Dimanche'] ? array(8, 15, 22) : array(7, 14, 21);
$debut = array(1, 8, 15);

if ($config['EDTSamedi'] == 1) {
    $config['nb_semaine'] = 2;
    $cellule = array("Semaine standard", "Semaine<br/>avec samedi");
    $table_name = array('Emploi du temps standard', 'Emploi du temps des semaines avec samedi travaillé');
} elseif ($config['EDTSamedi'] == 2) {
    $this->config('nb_semaine', 3);
    $cellule=array("Semaine standard", "Semaine<br/>avec samedi", "Semaine<br/>ouverture restreinte");
    $table_name = array('Emploi du temps standard', 'Emploi du temps des semaines avec samedi travaillé', 'Emploi du temps en ouverture restreinte');
}

for ($j = 0; $j < $nb_semaine; $j++) {
    if ($config['EDTSamedi']) {
        $hours_tab .= "<br/><b>{$table_name[$j]}</b>";
    }
    $hours_tab .= "<table border='1' cellspacing='0'>\n";
    $hours_tab .= "<tr style='text-align:center;'><td style='width:135px;'>{$cellule[$j]}</td><td style='width:135px;'>Heure d'arrivée</td>";
    if ($config['PlanningHebdo-Pause2']) {
        $hours_tab .= "<td style='width:135px;'>Début de pause 1</td><td style='width:135px;'>Fin de pause 1</td>";
        $hours_tab .= "<td style='width:135px;'>Début de pause 2</td><td style='width:135px;'>Fin de pause 2</td>";
    } else {
        $hours_tab .= "<td style='width:135px;'>Début de pause</td><td style='width:135px;'>Fin de pause</td>";
    }
    $hours_tab .= "<td style='width:135px;'>Heure de départ</td>";
    if ($config['Multisites-nombre']>1) {
        $hours_tab .= "<td>Site</td>";
    }
  
    $hours_tab .= "<td style='width:135px;'>Temps</td>";
    $hours_tab .= "</tr>\n";
    for ($i=$debut[$j];$i<$fin[$j];$i++) {
        $k=$i-($j*7)-1;
        if (in_array(21, $droits) and !$config['PlanningHebdo']) {
            $hours_tab .= "<tr><td>{$jours[$k]}</td>\n";
            $hours_tab .= "<td>".selectTemps($i-1, 0, null, "select$j")."</td>\n";
            $hours_tab .= "<td>".selectTemps($i-1, 1, null, "select$j")."</td>\n";
            $hours_tab .= "<td>".selectTemps($i-1, 2, null, "select$j")."</td>\n";
            if ($config['PlanningHebdo-Pause2']) {
                $hours_tab .= "<td>".selectTemps($i-1, 5, null, "select$j")."</td>\n";
                $hours_tab .= "<td>".selectTemps($i-1, 6, null, "select$j")."</td>\n";
            }
            $hours_tab .= "<td>".selectTemps($i-1, 3, null, "select$j")."</td>\n";
            if ($config['Multisites-nombre']>1) {
                $hours_tab .= "<td><select name='temps[".($i-1)."][4]' class='edt-site'>\n";
                $hours_tab .= "<option value='' class='edt-site-0'>&nbsp;</option>\n";
                for ($l=1;$l<=$config['Multisites-nombre'];$l++) {
                    $selected = (isset($temps[$i-1][4]) and $temps[$i-1][4]==$l) ? "selected='selected'" : null;
                    $hours_tab .= "<option value='$l' $selected class='edt-site-$l'>{$config["Multisites-site{$l}"]}</option>\n";
                }
                $hours_tab .= "</select></td>";
            }
            $hours_tab .= "<td id='heures_{$j}_$i'></td>\n";
            $hours_tab .= "</tr>\n";
        } else {
            $hours_tab .= "<tr><td>{$jours[$k]}</td>\n";

            for ($l=0; $l<3; $l++) {
                $heure = isset($temps[$i-1][0]) ? heure2($temps[$i-1][$l]) : null;
                $hours_tab .= "<td id='temps_".($i-1)."_$l'>$heure</td>\n";
            }

            if ($config['PlanningHebdo-Pause2']) {
                for ($l=5; $l<7; $l++) {
                    $heure = isset($temps[$i-1][$l]) ? heure2($temps[$i-1][$l]) : null;
                    $hours_tab .= "<td id='temps_".($i-1)."_$l'>$heure</td>\n";
                }
            }

            $heure = isset($temps[$i-1][0]) ? heure2($temps[$i-1][3]) : null;
            $hours_tab .= "<td id='temps_".($i-1)."_3'>$heure</td>\n";


            if ($config['Multisites-nombre']>1) {
                $site=null;
                if (isset($temps[$i-1][4])) {
                    $site="Multisites-site".$temps[$i-1][4];
                    $site = isset($config[$site]) ? $config[$site] : null;
                }
                $hours_tab .= "<td>$site</td>";
            }
            $hours_tab .= "<td id='heures_{$j}_$i'></td>\n";
            $hours_tab .= "</tr>\n";
        }
    }
    $hours_tab .= "</table>\n";
    $hours_tab .= "Total : <font style='font-weight:bold;' id='heures$j'></font><br/><br/>\n";
}

// EDTSamedi : emploi du temps différents les semaines avec samedi travaillé
// Choix des semaines avec samedi travaillé
if ($this->config('EDTSamedi')) {
    // Recherche des semaines avec samedi travaillé entre le 1er septembre de N-1 et le 31 août de N+3
    $d = new datePl( (date("Y") -1) . "-09-01");
    $premierLundi = $d->dates[0];
    $d = new datePl( (date("Y") +3) . "-08-31");
    $dernierLundi = $d->dates[0];

    $p = new personnel();
    $p->fetchEDTSamedi($id, $premierLundi, $dernierLundi);
    $edt = $p->elements;

    // inputs premierLundi et dernierLundi pour mise à jour (validation=suppression et insertion des nouveaux élements)
    $hours_tab .= "<input type='hidden' name='premierLundi' value='$premierLundi' />\n";
    $hours_tab .= "<input type='hidden' name='dernierLundi' value='$dernierLundi' />\n";
    $hours_tab .= "<div id='EDTChoix'>\n";
    $hours_tab .= "<h3>Choix des emplois du temps</h3>\n";

    if ($this->config('EDTSamedi') == 1) {
        $hours_tab .= "<p>Cochez les semaines avec le samedi travaill&eacute;</p>\n";
    } elseif ($config['EDTSamedi'] == 2) {
        $hours_tab .= "<p>Pour chaque semaine, cochez s'il s'agit d'une semaine : standard (STD) / avec samedi travaill&eacute; (SAM) / ouverture restreinte (RES)</p>\n";
    }

    $hours_tab .= "<div id='EDTTabs'>\n";
    $hours_tab .= "<ul>";
    for ($i = 0; $i < 4; $i++) {
        $annee = (date("Y") + $i -1) . "-" . (date("Y") + $i);
        $hours_tab .= "<li><a href='#EDTTabs-$i' id='EDTA-$i'>Année $annee</a></li>\n";
    }
    $hours_tab .= "</ul>\n";

    for ($i=0;$i<4;$i++) {
        $d=new datePl((date("Y")-1+$i)."-09-01");
        $premierLundi=$d->dates[0];
        $d=new datePl((date("Y")+$i)."-08-31");
        $dernierLundi=$d->dates[0];

        if (date("Y-m-d")>=$premierLundi and date("Y-m-d")<=$dernierLundi) {
            $currentTab="#EDTA-$i";
        }
        $current=$premierLundi;
        $j=0;

        $hours_tab .= "<div id=EDTTabs-$i>";
        $hours_tab .= "<table class='tableauStandard'>";
        $hours_tab .= "<tr><td>";

        while ($current <= $dernierLundi) {
            // Evite de mettre la même semaine (fin août - début septembre) dans 2 années universitaires
            if (isset($last) and $current==$last) {
                $last=$current;
                $current=date("Y-m-d", strtotime("+7 day", strtotime($current)));
                continue;
            }
            $lundi=date("d/m/Y", strtotime($current));
            $dimanche=date("d/m/Y", strtotime("+6 day", strtotime($current)));
            $semaine=date("W", strtotime($current));
            $hours_tab .= "S$semaine : $lundi &rarr; $dimanche";

            // Si config['EDTSamedi'] == 1 (Emploi du temps différent les semaines avec samedi travaillé)
            if ($config['EDTSamedi'] == 1) {
                $checked = array_key_exists($current, $edt) ? "checked='checked'" : null ;
                $hours_tab .= "<input type='checkbox' value='$current' name='EDTSamedi[]' $checked /><br/>\n";
            }

            // Si config['EDTSamedi'] == 2 (Emploi du temps différent les semaines avec samedi travaillé et en ouverture restreinte)
            elseif ($config['EDTSamedi'] == 2) {
                $checked1 = "checked='checked'";
                $checked2 = null;
                $checked3 = null;

                if (array_key_exists($current, $edt)) {
                    $checked1 = null;
          
                    if ($edt[$current]['tableau'] == 2) {
                        $checked2 = "checked='checked'";
                    } elseif ($edt[$current]['tableau'] == 3) {
                        $checked3 = "checked='checked'";
                    }
                }

                $hours_tab .= "<span style='margin-left:20px;'>\n";
                $hours_tab .= "<input type='radio' value='1' name='EDTSamedi_$current' $checked1 id='radio_{$current}_STD'/> <label for='radio_{$current}_STD' >STD</label>\n";
                $hours_tab .= "<input type='radio' value='2' name='EDTSamedi_$current' $checked2 id='radio_{$current}_SAM'/> <label for='radio_{$current}_SAM' >SAM</label>\n";
                $hours_tab .= "<input type='radio' value='3' name='EDTSamedi_$current' $checked3 id='radio_{$current}_RES'/> <label for='radio_{$current}_RES' >RES</label>\n";
                $hours_tab .= "</span>\n";
                $hours_tab .= "<br/>\n";
            }

            if ($j==17 or $j==35) {
                $hours_tab .= "</td><td>";
            }
            $j++;
            $last=$current;
            $current=date("Y-m-d", strtotime("+7 day", strtotime($current)));
        }
        $hours_tab .= "</td></tr>\n";
        $hours_tab .= "</table>\n";
        $hours_tab .= "</div>\n";
    }
    $hours_tab .= "</div>\n";
    $hours_tab .= "</div>\n";
}
