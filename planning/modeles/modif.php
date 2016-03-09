<?php
/**
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/modeles/modif.php
Création : mai 2011
Dernière modification : 7 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche le fomulaire permettant de renommer un modèle..
Ce formulaire est soumis à la page planning/modeles/valid.php

Cette page est appelée par le fichier index.php
*/

require_once "class.modeles.php";

echo "<h3>Modification du modèle</h3>\n";

$nom=filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING);
$nom_origine=$nom;
?>
<form method='get' action='index.php' name='form'>
<input type='hidden' name='page' value='planning/modeles/valid.php' />
<input type='hidden' name='action' value='modif' />
<?php echo "<input type='hidden' name='nom_origine' value='$nom_origine' />\n"; ?>
<table class='tableauFiches'>
<tr>
<td>Nom du modèle :</td>
<?php echo "<td><input type='text' value='$nom' name='nom' class='ui-corner-all ui-widget-content' /></td>\n"; ?>
</tr>
<tr><td colspan='2' style='text-align:center;'>
<br/><input type='button' value='Annuler' onclick='history.go(-1);' class='ui-button'/>
&nbsp;&nbsp;&nbsp;
<input type='submit' value='Valider' class='ui-button'/>
</td></tr>
</table>
</form>