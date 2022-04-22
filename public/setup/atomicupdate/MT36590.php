<?php

$sql[]="UPDATE `{$dbprefix}config` SET `commentaires` = 'Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte. Ce paramètre est ignoré si PlanningHebdo est activé.' WHERE `nom`='EDTSamedi';";