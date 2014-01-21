<?php
/*
Planning Biblio, Version 1.6.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/tableaux.php
Création : 21 janvier 2014
Dernière modification : 21 janvier 2014
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
  <h3>Choix du nombre de tableaux</h3>
  <form name='form' action='index.php' method='get'>
  <input type='hidden' name='id' id='id' value='$tableauNumero' />
  <table class='tableauFiche'>
  <tr><td>Nombre de tableaux</td>
    <td><select name='nombre' id='nombre' onchange='tableauxNombre();'>
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