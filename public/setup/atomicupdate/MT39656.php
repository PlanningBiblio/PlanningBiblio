<?php

$sql[] = "UPDATE `{$dbprefix}cron` SET `comments` = 'Reset holiday remainders' WHERE `command` = 'cron.holiday_reset_remainder.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET `comments` = 'Reset holiday credits' WHERE `command` = 'cron.holiday_reset_credits.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET `comments` = 'Reset holiday compensatory time' WHERE `command` = 'cron.holiday_reset_comp_time.php';";

