<?php

$sql[] = "UPDATE `{$dbprefix}absences` SET `cal_name` = REPLACE(`cal_name`, 'PlanningBiblio-Absences-', 'MSGraph-'), `valide` = '99999', `valide_n1` = '99999', `validation` = `demande`, `validation_n1` = `demande` WHERE `cal_name` like 'PlanningBiblio-Absences%' AND `ical_key` NOT LIKE '%T%_%T%Z_%T%_%Z';";
