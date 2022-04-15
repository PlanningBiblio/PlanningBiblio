<?php

$sql[] = "UPDATE `{$dbprefix}conges` SET `fin` = REPLACE(`fin`, '23:59:00', '23:59:59') WHERE fin like '%23:59:00';";
$sql[] = "UPDATE `{$dbprefix}absences` SET `fin` = REPLACE(`fin`, '23:59:00', '23:59:59') WHERE fin like '%23:59:00';";