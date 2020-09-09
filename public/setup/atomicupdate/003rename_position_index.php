<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/position'  WHERE  `page` = 'postes/index.php' ;";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/position'  WHERE  `url` = 'postes/index.php' ;";

?>
