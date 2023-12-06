<?php
// Updates configuration option dateDebutPlHebdo to maintain week number in cycle

// For nb_semaine >= 5, week cycles are not reset on new year anymore.
// This script updates dateDebutPlHebdo to keep a continuity in week cycles
// between the previous behavior and the current one.

// This script can be run multiple times:
// dateDebutPlHebdo will not be changed if ran in the same week
// dateDebutPlHebdo will possibly change if ran in different weeks

// See MT42875 for reference

require_once(__DIR__ . '/../init/init.php');
require_once(__DIR__ . '/../public/include/db.php');

$nb_semaine = $GLOBALS['config']['nb_semaine'];
if ($nb_semaine >= 5) {
    print "nb_semaine: $nb_semaine\n";
    $dateDebutPl = $GLOBALS['config']['dateDebutPlHebdo'];

    $currentDate = new DateTime('now');
    $currentDatePl = new DatePl($currentDate->format('Y-m-d'));

    $oldStartDate = DateTime::createFromFormat('d/m/Y', $dateDebutPl);
    $newStartDate = DateTime::createFromFormat('d/m/Y', $dateDebutPl);

    $oldWeekInCycle = $currentDatePl->getCycleNumber($currentDatePl->semaine, $nb_semaine);
    $newWeekInCycle = $currentDatePl->getCycleNumber($currentDatePl->getNumberOfWeeksSinceStartDate($currentDatePl->date), $nb_semaine);

    if ($oldWeekInCycle != $newWeekInCycle) {
        print "old dateDebutPlHebdo: " . $oldStartDate->format('d/m/Y') . "\n";
        print "week number in cycle with the old behavior: $oldWeekInCycle\n";
        print "week number in cycle with the new behavior: $newWeekInCycle\n";
        if ($oldWeekInCycle > $newWeekInCycle) {
            $difference = $oldWeekInCycle - $newWeekInCycle; 
            $newStartDate->modify("-$difference week");
        } else if ($newWeekInCycle > $oldWeekInCycle) {
            $difference = $newWeekInCycle - $oldWeekInCycle; 
            $newStartDate->modify("+$difference week");
        }
        print "new dateDebutPlHebdo: " . $newStartDate->format('d/m/Y') . "\n";
        $db = new db();
        $query = "UPDATE config SET valeur='" . $newStartDate->format('d/m/Y') .  "' WHERE nom='dateDebutPlHebdo' LIMIT 1;";
        $db->query($query);
        $GLOBALS['config']['dateDebutPlHebdo'] = $newStartDate->format('d/m/Y');

        $currentDatePl = new DatePl($currentDate->format('Y-m-d'));
        print "new week number in cycle: " . $currentDatePl->semaine3 . "\n"; 

    } else {
        print "The week number in the cycles are the same ($oldWeekInCycle): nothing to do\n";
    }
} else {
    print "nb_semaine: $nb_semaine (<5): nothing to do\n";
}
