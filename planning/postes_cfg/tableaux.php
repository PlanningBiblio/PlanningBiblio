<?php
/*
Planning Biblio, Version 1.7.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/tableaux.php
Création : 21 janvier 2014
Dernière modification : 22 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de choisir le nombre de tableaux avec horaires à intégrer dans le planning.

Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/

require_once "class.tableaux.php";
$t=new tableau();
$t->id=$tableauNumero;
$t->getNumbers();

$nombre=$t->length;

echo <<<EOD
  <form name='form' action='index.php' method='get'>
  <input type='hidden' name='id' id='id' value='$tableauNumero' />
  <table><tr><td style='width:600px;'>
    <h3>Choix du nombre de tableaux</h3>
    </td><td style='text-align:right;'>
      <input type='button' value='Retour' class='ui-button retour'/>
      <input type='button' value='Valider' class='ui-button' onclick='tableauxNombre();'/>
  </td></tr></table>

  <table class='tableauFiche'>
    <tr><td>Nombre de tableaux :</td>
      <td><select name='nombre' id='nombre'>
EOD;
  for($i=1;$i<16;$i++){
    $selected=$i==$nombre?"selected='selected'":null;
    echo "<option value='$i' $selected >$i</option>\n";
  }
echo <<<EOD
    </select></td></tr>
  </table>
  </form>

EOD;
?>