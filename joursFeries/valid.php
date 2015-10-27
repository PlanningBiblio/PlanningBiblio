<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : joursFeries/valid.php
Création : 25 juillet 2013
Dernière modification : 25 juillet 2013
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Fichier permettant la validation des jours fériés et de fermeture.
Valide le formualire de la page joursFeries/index.php
*/

include "class.joursFeries.php";

$j=new joursFeries();
$j->update($_POST);
$message=$j->error?"Erreur":"OK";

echo "<script type='text/JavaScript'>document.location.href='index.php?page=joursFeries/index.php&message=$message';</script>\n";
?>