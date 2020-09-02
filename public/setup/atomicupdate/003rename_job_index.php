<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/job'  WHERE  `page` = 'postes/index.php' ;";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/job'  WHERE  `url` = 'postes/index.php' ;";

?>
