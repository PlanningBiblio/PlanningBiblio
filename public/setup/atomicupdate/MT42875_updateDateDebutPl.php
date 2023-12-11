<?php

// Updates configuration option dateDebutPlHebdo to maintain week number in cycle

// For nb_semaine >= 5, week cycles are not reset on new year anymore.
// This script updates dateDebutPlHebdo to keep a continuity in week cycles
// between the previous behavior and the current one.

$db = new db();
$db->select2('config', array('valeur'), array('nom' => 'MT42875_dateDebutPlHebdo'));
if (!$db->result) {
    $query = '';
    $summary = '';
    $nb_semaine = $GLOBALS['config']['nb_semaine'];
    if ($nb_semaine >= 5) {
        $currentDate = new DateTime('now');
        $summary .= "Execution date: " . $currentDate->format('Y-m-d') . "\n";

        $summary .= "nb_semaine: $nb_semaine\n";
        $dateDebutPl = $GLOBALS['config']['dateDebutPlHebdo'];

        $currentDatePl = new DatePl($currentDate->format('Y-m-d'));

        $oldStartDate = DateTime::createFromFormat('d/m/Y', $dateDebutPl);
        $newStartDate = DateTime::createFromFormat('d/m/Y', $dateDebutPl);

        $oldWeekInCycle = $currentDatePl->getCycleNumber($currentDatePl->semaine, $nb_semaine);
        $newWeekInCycle = $currentDatePl->getCycleNumber($currentDatePl->getNumberOfWeeksSinceStartDate($currentDatePl->date), $nb_semaine);

        if ($oldWeekInCycle != $newWeekInCycle) {
            $summary .= "old dateDebutPlHebdo: " . $oldStartDate->format('d/m/Y') . "\n";
            $summary .= "week number in cycle with the old behavior: $oldWeekInCycle\n";
            $summary .= "week number in cycle with the new behavior: $newWeekInCycle\n";
            if ($oldWeekInCycle > $newWeekInCycle) {
                $difference = $oldWeekInCycle - $newWeekInCycle;
                $newStartDate->modify("-$difference week");
            } else if ($newWeekInCycle > $oldWeekInCycle) {
                $difference = $newWeekInCycle - $oldWeekInCycle;
                $newStartDate->modify("+$difference week");
            }

            $sql[] = "UPDATE config SET valeur='" . $newStartDate->format('d/m/Y') .  "' WHERE nom='dateDebutPlHebdo' LIMIT 1;";
            $summary .= "new dateDebutPlHebdo: " . $newStartDate->format('d/m/Y') . "\n";

            $GLOBALS['config']['dateDebutPlHebdo'] = $newStartDate->format('d/m/Y');
            $currentDatePl = new DatePl($currentDate->format('Y-m-d'));
            $summary .= "new week number in cycle: " . $currentDatePl->semaine3 . "\n";

        } else {
            $summary .= "The week number in the cycles are the same ($oldWeekInCycle): nothing to do\n";
        }
    } else {
        $summary .= "nb_semaine: $nb_semaine (<5): nothing to do\n";
    }

    $sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('MT42875_dateDebutPlHebdo', 'hidden', '$summary', '', '', 'Heures de présence', 0);";
}

