<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/index.php
Création : 24 juillet 2013
Dernière modification : 28 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier Index du dossier congés, Affiche les liens vers les autres pages du dossier
Accessible par le menu congés
Inclus dans le fichier index.php
*/

require_once "class.conges.php";
?>

<h3>Les congés</h3>
<table>
<tr style='vertical-align:top;'>
<td style='width:400px;'>
<ul style='margin-top:0px;'>
<li><a href='index.php?page=conges/voir.php'>Liste des congés</a></li>
<li><a href='index.php?page=conges/enregistrer.php'>Poser des congés</a></li>
<li><a href='index.php?page=conges/recuperations.php'>R&eacute;cup&eacute;rations</a></li>
<?php

// Gestion des droits d'administration
// NOTE : Ici, pas de différenciation entre les droits niveau 1 et niveau 2
// NOTE : Les agents ayant les droits niveau 1 ou niveau 2 sont admin ($admin, droits 40x et 60x)
// TODO : différencier les niveau 1 et 2 si demandé par les utilisateurs du plugin

$admin = false;
for ($i = 1; $i <= $config['Multisites-nombre']; $i++) {
    if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
        $admin = true;
        break;
    }
}

if ($admin) {
    echo "<li><a href='index.php?page=conges/infos.php'>Ajouter une information</a></li>\n";
    echo "<li><a href='index.php?page=conges/credits.php'>Voir les cr&eacute;dits</a></li>\n";
}
echo "</ul>\n";
echo "</td>\n";
echo "<td style='color:#FF5E0E;'>\n";

$date=date("Y-m-d");
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
if ($db->result) {
    echo "<b>Informations sur les congés :</b><br/><br/>\n";
    foreach ($db->result as $elem) {
        if ($admin) {
            echo "<a href='index.php?page=conges/infos.php&amp;id={$elem['id']}'><span class='pl-icon pl-icon-edit' title='Modifier'></span></a>&nbsp;";
        }
        echo "Du ".dateFr($elem['debut'])." au ".dateFr($elem['fin'])." : <br/>".str_replace("\n", "<br/>", $elem['texte'])."<br/><br/>\n";
    }
}
?>
</td></tr></table>