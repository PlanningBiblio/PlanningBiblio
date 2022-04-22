<?php
use App\PlanningBiblio\Helper\HolidayHelper;

# Very very tricky solution but this is this fatsest
# way to twigizing this part.
$config = $GLOBALS['config'];
$temps = $GLOBALS['temps'];
$breaktimes = $GLOBALS['breaktimes'];
$hours_tab = '';

if ($config['PlanningHebdo']) {
    $holiday_helper = new HolidayHelper(array("perso_id" => $id));
    $result = $holiday_helper->getPlanning();
    $nb_semaine = $result['nb_semaine'] ?? $config['nb_semaine'];
} else {
    $nb_semaine = $config['nb_semaine'];
}

switch ($nb_semaine) {
  case 1:
    $cellule = array('Jour');
    break;
  case 2:
    $cellule = array(
        'Semaine Impaire',
        'Semaine Paire'
        );
    break;
  default:
    $cellule = array();
    for ($i = 1; $i <= $nb_semaine; $i++) {
        $cellule[] = 'Semaine ' . $i;
    }
    break;
}
$fin = $config['Dimanche'] ? array(7, 14, 21, 28, 36, 42, 49, 56, 63, 70) : array(6, 13, 20, 27, 35, 41, 48, 55, 62, 69);
$debut = array(1, 8, 15, 22, 29, 36, 43, 50, 57, 64);

// EDTSamedi works only if PlanningHebdo is disabled.
$EDTSamedi = $this->config('PlanningHebdo') ? 0 : $this->config('EDTSamedi');

if ($EDTSamedi == 1) {
    $config['nb_semaine'] = 2;
    $cellule = array("Semaine standard", "Semaine<br/>avec samedi");
    $table_name = array('Emploi du temps standard', 'Emploi du temps des semaines avec samedi travaillé');
} elseif ($EDTSamedi == 2) {
    $this->config('nb_semaine', 3);
    $cellule=array("Semaine standard", "Semaine<br/>avec samedi", "Semaine<br/>ouverture restreinte");
    $table_name = array('Emploi du temps standard', 'Emploi du temps des semaines avec samedi travaillé', 'Emploi du temps en ouverture restreinte');
}

for ($j = 0; $j < $nb_semaine; $j++) {
    if ($EDTSamedi) {
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

    if ($config['PlanningHebdo-PauseLibre'] && $config['PlanningHebdo']) {
        $hours_tab .= "<td>Temps de pause</td>";
    }

    if ($config['Multisites-nombre']>1) {
        $hours_tab .= "<td>Site</td>";
    }
  
    $hours_tab .= "<td style='width:135px;'>Temps</td>";
    $hours_tab .= "</tr>\n";

    $disabled = "disabled";
    if (in_array(21, $droits) and !$config['PlanningHebdo']) {
        $disabled = "";
    }
    for ($i = $debut[$j]; $i <= $fin[$j]; $i++) {
        $k=$i-($j*7)-1;
        $t = $i - 1;
        $hours_tab .= "<tr><td>{$jours[$k]}</td>\n";

        if (isset($temps[$t])) {
            foreach ($temps[$t] as $index => $time) {
                // Site index.
                if ($index == 4) {
                    continue;
                }
                $tmp = '';
                if ($time) {
                    $tmp = (new \DateTime($time))->format("H:i");
                }
                $temps[$t][$index] = $tmp;
            }
        }

        // Arriving time (index 0).
        $hours_tab .= "<td>";
        $hours_tab .= "<input name='temps[{$t}][0]' $disabled ";
        $hours_tab .= "class='planno-timepicker select$j wh-timepicker'";
        $t0 = isset($temps[$t][0]) ? $temps[$t][0] : '';
        $hours_tab .= "value='$t0'/>";
        $hours_tab .= "</td>\n";

        // Start of break 1.
        $hours_tab .= "<td>";
        $hours_tab .= "<input name='temps[{$t}][1]' $disabled ";
        $hours_tab .= "class='planno-timepicker select$j wh-timepicker'";
        $t1 = isset($temps[$t][1]) ? $temps[$t][1] : '';
        $hours_tab .= "value='$t1'/>";
        $hours_tab .= "</td>\n";

        // End of break 1.
        $hours_tab .= "<td>";
        $hours_tab .= "<input name='temps[{$t}][2]' $disabled ";
        $hours_tab .= "class='planno-timepicker select$j wh-timepicker'";
        $t2 = isset($temps[$t][2]) ? $temps[$t][2] : '';
        $hours_tab .= "value='$t2'/>";
        $hours_tab .= "</td>\n";

        if ($config['PlanningHebdo-Pause2']) {
            // Start of break 1.
            $hours_tab .= "<td>";
            $hours_tab .= "<input name='temps[{$t}][5]' $disabled ";
            $hours_tab .= "class='planno-timepicker select$j wh-timepicker'";
            $t5 = isset($temps[$t][5]) ? $temps[$t][5] : '';
            $hours_tab .= "value='$t5'/>";
            $hours_tab .= "</td>\n";

            // End of break 1.
            $hours_tab .= "<td>";
            $hours_tab .= "<input name='temps[{$t}][6]' $disabled ";
            $hours_tab .= "class='planno-timepicker select$j wh-timepicker'";
            $t6 = isset($temps[$t][6]) ? $temps[$t][6] : '';
            $hours_tab .= "value='$t6'/>";
            $hours_tab .= "</td>\n";
        }

        // Departure time.
        $hours_tab .= "<td>";
        $hours_tab .= "<input name='temps[{$t}][3]' $disabled ";
        $hours_tab .= "class='planno-timepicker select$j wh-timepicker'";
        $t3 = isset($temps[$t][3]) ? $temps[$t][3] : '';
        $hours_tab .= "value='$t3'/>";
        $hours_tab .= "</td>\n";

        if ($config['PlanningHebdo-PauseLibre'] && $config['PlanningHebdo']) {
            $breaktime = isset($breaktimes[$i -1]) ? $breaktimes[$i -1] : '';
            $hours_tab .= '<td id="break_' . ($i-1) . '" data-break="' . $breaktime . '">';
            $hours_tab .= "<input name='break_{$t}' $disabled ";
            $hours_tab .= "class='planno-break-timepicker'";
            $hours_tab .= "value='$breaktime'/>";
            $hours_tab .= '</td>';
        }

        if ($config['Multisites-nombre']>1) {
            if ($disabled) {
                $site=null;
                if (isset($temps[$i-1][4])) {
                    $site="Multisites-site".$temps[$i-1][4];
                    $site = isset($config[$site]) ? $config[$site] : null;

                    $site = $temps[$i-1][4] == -1 ? 'Tout site' : $site;
                }
                $hours_tab .= "<td>&nbsp;$site&nbsp;</td>";
            } else {
                $hours_tab .= "<td><select name='temps[".($i-1)."][4]' class='edt-site'>\n";
                $hours_tab .= "<option value='' class='edt-site-0'>&nbsp;</option>\n";
                for ($l=1;$l<=$config['Multisites-nombre'];$l++) {
                    $selected = (isset($temps[$i-1][4]) and $temps[$i-1][4]==$l) ? "selected='selected'" : null;
                    $hours_tab .= "<option value='$l' $selected class='edt-site-$l'>{$config["Multisites-site{$l}"]}</option>\n";
                }
                $selected = (isset($temps[$i-1][4]) and $temps[$i-1][4] == -1) ? "selected='selected'" : null;
                $hours_tab .= "<option value='-1' $selected class='edt-site--1'>Tout site</option>\n";
                $hours_tab .= "</select></td>";
            }
        }
        $hours_tab .= "<td id='heures_{$j}_$i'></td>\n";
        $hours_tab .= "</tr>\n";
    }
    $hours_tab .= "</table>\n";
    $hours_tab .= "Total : <font style='font-weight:bold;' id='heures$j'></font><br/><br/>\n";
}

// EDTSamedi : emploi du temps différents les semaines avec samedi travaillé
// Choix des semaines avec samedi travaillé
if ($EDTSamedi) {
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

    if ($EDTSamedi == 1) {
        $hours_tab .= "<p>Cochez les semaines avec le samedi travaill&eacute;</p>\n";
    } elseif ($EDTSamedi == 2) {
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
            if ($EDTSamedi == 1) {
                $checked = array_key_exists($current, $edt) ? "checked='checked'" : null ;
                $hours_tab .= "<input type='checkbox' value='$current' name='EDTSamedi[]' $checked /><br/>\n";
            }

            // Si config['EDTSamedi'] == 2 (Emploi du temps différent les semaines avec samedi travaillé et en ouverture restreinte)
            elseif ($EDTSamedi == 2) {
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
