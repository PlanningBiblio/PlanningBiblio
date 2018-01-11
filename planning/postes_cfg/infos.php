<?php
/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/postes_cfg/infos.php
Création : 20 février 2016
Dernière modification : 20 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de choisir le nombre de tableaux avec horaires à intégrer dans le planning.

Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/
require_once "class.tableaux.php";

// Nombre de tableaux
$t=new tableau();
$t->id=$tableauNumero;
$t->getNumbers();
$nombre=$t->length;


// Site
if($config['Multisites-nombre']>1){
  $db=new db();
  $db->select("pl_poste_tab","*","tableau='$tableauNumero'");
  $site=$db->result[0]['site'];
}

echo <<<EOD
  <div style='min-height:350px;'>
  <form name='form' action='index.php' method='get'>
  <input type='hidden' name='id' id='id' value='$tableauNumero' />

  <h3>Informations générales</h3>

  <table class='tableauFiche'>
  
  <tr><td>Nom :</td>
  <td><input type='text' id='nom' value='$tableauNom'  style='width:300px;'/></td></tr>
EOD;

if($config['Multisites-nombre']>1){
  echo "<tr><td>Affecter au site :</td>\n";
  echo "<td><select id='site' style='width:300px;'>\n";
  echo "<option value=''>&nbsp;</option>\n";

  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    $selected=$i==$site?"selected='selected'":null;
    echo "<option value='$i' $selected >".$config["Multisites-site$i"]."</option>\n";
  }
  echo "</select>\n";
  echo "</td></tr>\n";
}else{
  echo "<input type='hidden' value='1' id='site' />\n";
}
  
echo "<tr><td>Nombre de tableaux :</td>\n";
echo "<td><select name='nombre' id='nombre' style='width:300px;'>\n";

for($i=1;$i<16;$i++){
  $selected=$i==$nombre?"selected='selected'":null;
  echo "<option value='$i' $selected >$i</option>\n";
}
echo "</select></td></tr>\n";

echo "</table>\n";
echo "</form>\n";
?>
</div>
<p class='important'>Important : Vous devez cliquer sur "Valider" avant de changer d'onglet</p>