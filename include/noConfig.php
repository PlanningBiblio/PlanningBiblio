<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/noConfig.php
Création : 8 avril 2015
Dernière modification : 8 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche une page renvoyant vers le fichier setup/index.php si le fichier de configuration est absent

Page appelée (include) par les fichiers authentification.php et index.php si le fichier include/config.php est absent
*/

include "header.php";
?>
<div id='auth-logo'></div>
<h2 id='h2-authentification'>Fichier de configuration manquant</h2>
<center>
<strong>
Le fichier de configuration est manquant.<br/> 
<a href='setup/index.php'>Cliquez ici pour commencer l'installation.</a>
</strong>
</center>
<?php
include "include/footer.php";
exit;
?>