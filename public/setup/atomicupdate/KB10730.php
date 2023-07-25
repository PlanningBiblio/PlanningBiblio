<?php

$sql[] = "ALTER TABLE `{$dbprefix}conges` CHANGE `debit` `debit` VARCHAR(30);";

if ($config['Conges-Recuperations'] == 1) {
    $sql[] = "UPDATE `{$dbprefix}conges` SET `debit` = 'recuperations' WHERE `debit` = 'recuperation';";
    $sql[] = "UPDATE `{$dbprefix}conges` SET `debit` = 'reliquat-conges' WHERE `debit` = 'credit';";
} else {
    $sql[] = "UPDATE `{$dbprefix}conges` SET `debit` = 'reliquat-recuperations-conges' WHERE `debit` = 'recuperation';";
    $sql[] = "UPDATE `{$dbprefix}conges` SET `debit` = 'reliquat-conges-recuperations' WHERE `debit` = 'credit';";
}
