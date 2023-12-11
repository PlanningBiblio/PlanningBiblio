<?php

namespace App\Trait;

trait PlanningJobTrait
{
    protected function getAnimationTables($absences, $tabs, $debut, $fin, $tablePosition)
    {
        $config = $GLOBALS['config'];
        $animation_table = array();
        $agents = array();

        // Look for animations
        $animations = array();
        foreach ($absences as $abs) {
            if ( !empty($config['animations']) and in_array(strtolower($abs['motif']), $config['animations'])) {
                $animations[] = $abs;
            }
        }

        // Create a table for animations
        if (!empty($animations)) {

            // Group animations by hours
            $anim_tables = array();
            foreach ($animations as $anim) {
                $begin = preg_replace('/.* (.*)/', "$1", $anim['debut']);
                $end = preg_replace('/.* (.*)/', "$1", $anim['fin']);

                if ($begin < $debut) {
                    $begin = $debut;
                }

                if ($end > $fin) {
                    $end = $fin;
                }

                if (!array_key_exists($begin.'-'.$end, $anim_tables)) {
                    $anim_tables[$begin.'-'.$end] = array(
                        'begin' => $begin,
                        'end' => $end,
                        'animations' => array(),
                        );
                }

                if (!array_key_exists($anim['groupe'], $anim_tables[$begin.'-'.$end]['animations'])) {
                    $agents[$anim['groupe']] = array($anim['perso_id']);
                    $anim_tables[$begin.'-'.$end]['animations'][$anim['groupe']] = array ('animation' => $anim['commentaires'], 'agents' => $agents[$anim['groupe']]);
                } else {
                    $agents[$anim['groupe']][] = $anim['perso_id'];
                    $anim_tables[$begin.'-'.$end]['animations'][$anim['groupe']]['agents'] = $agents[$anim['groupe']];
                }
            }

            $animation_tables = array();

            foreach ($anim_tables as $table) {

                $hours = array();
                $lines = array();
                $cells = array();
                $offset1 = false;
                $offset2 = false;

                if ($table['begin'] > $debut) {
                    $hours[] = $this->getCellHours($debut, $table['begin']);
                    $offset1 = true;
                }

                $hours[] = $this->getCellHours($table['begin'], $table['end']);

                if ($table['end'] < $fin) {
                    $hours[] = $this->getCellHours($table['end'], $fin);
                    $offset2 = true;
                }

                $i = 0;
                foreach ($table['animations'] as $anim) {
                    
                    $cellContent = '';
                    foreach ($anim['agents'] as $key => $value) {
                        $cellContent .= nom($value) . '.';
                        if ($key != array_key_last($anim['agents'])) {
                            $cellContent .= '<br/>';
                        }
                    }

                    $timeSlot = array();
                    $j = 0;

                    if ($offset1) {
                        $colspan = $hours[$j++]['start_nb30'];
                        $timeSlot[] = array(
                            'cell_off' => 0,
                            'cell_html' => '<td colspan="' . $colspan . '" class="cellule_grise"></td>',
                        );
                    }

                    $colspan = $hours[$j++]['start_nb30'];
                    $timeSlot[] = array(
                        'cell_off' => 0,
                        'cell_html' => '<td colspan="' . $colspan . '" class="menuTrigger">' . $cellContent . '</td>',
                    );

                    if ($offset2) {
                        $colspan = $hours[$j++]['start_nb30'];
                        $timeSlot[] = array(
                            'cell_off' => 0,
                            'cell_html' => '<td colspan="' . $colspan . '" class="cellule_grise"></td>',
                        );
                    }

                    $lines[] = array(
                        'tableau' => '0',
                        'ligne' => $i,
                        'poste' => 0,
                        'type' => 'animation',
                        'emptyLine' => null,  // Added
                        'is_position' => 1,   // Added
                        'separation' => 0,    // Added
                        'class_td' => 'td_obligatoire',    // Added
                        'class_tr' => 'tr_obligatoire',    // Added
                        'position_name' => $anim['animation'], // Added, position name ???
                        'floor' => '',        // Added
                        'horaires' => $timeSlot,
                        );

                    if ($offset1) {
                        $cells[] = $i."_1";
                    }

                    if ($offset2 and !$offset1) {
                        $cells[] = $i."_2";
                    }

                    if ($offset2 and $offset1) {
                        $cells[] = $i."_3";
                    }

                    $i++;
                }

                $animation_table[] = array(
                    'nom' => '0',
                    'titre' => 'Animations',
                    'classe' => 'violet',
                    'horaires' => $hours,
                    'lignes' => $lines,
                    'cellules_grises' => $cells,
                    'hiddenTable' => null,  // Added
                    'j' => $tablePosition,  // Added
                    'titre2' => 'Animations', // Added
                    'masqueTableaux' => "",  // Added
                    'colspan' => 0, // Added
                    );
            }

            foreach ($animation_table as $anim_tab) {
                array_unshift($tabs, $anim_tab);
            }
        }

        return array($animation_table, $agents);
    }


    private function getCellHours($start, $end)
    {
        return array(
            'debut' => $start,
            'fin' => $end,
            'start_nb30' => nb30($start, $end),
            'start_h3' => heure3($start),
            'end_h3' => heure3($end)
        );
    }
}
