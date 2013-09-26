<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.7
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : planning/modeles/modif.php												*
* Création : mai 2011														*
* Dernière modification : 16 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Affiche le fomulaire permettant de renommer un modèle..									*
* Ce formulaire est soumis à la page planning/modeles/valid.php									*
*																*
* Cette page est appelée par le fichier index.php										*
*********************************************************************************************************************************/

require_once "class.modeles.php";

echo "<h3>Modification du modèle</h3>\n";

$nom=$_GET['nom'];
$nom_origine=$_GET['nom'];
$db=new db();
?>
<form method='get' action='index.php' name='form'>
<input type='hidden' name='page' value='planning/modeles/valid.php' />
<input type='hidden' name='action' value='modif' />
<?php echo "<input type='hidden' name='nom_origine' value='$nom_origine' />\n"; ?>
<table style='width:400px'>
<tr>
<td style='width:150px'>Nom du modèle :</td>
<?php echo "<td><input type='text' value='$nom' name='nom' style='width:250px'/></td>\n"; ?>
</tr>
<tr><td colspan='2' style='text-align:center;'>
<br/><input type='button' value='Annuler' onclick='history.go(-1);'/>
&nbsp;&nbsp;&nbsp;
<input type='submit' value='Valider'/>
</td></tr>
</table>
</form>