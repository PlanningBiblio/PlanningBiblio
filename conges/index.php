<?php
/**
Planning Biblio, Plugin Congés Version 2.7.06
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/index.php
Création : 24 juillet 2013
Dernière modification : 30 novembre 2017
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
$required=array(2,7,401,402,403,404,405,406,407,408,409,410,601,602,603,604,605,606,607,608,609,610);
$admin=false;
foreach ($required as $elem) {
    if (in_array($elem, $droits)) {
        $admin=true;
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