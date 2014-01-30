<?php
/*
Planning Biblio, Version 1.6.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/horaires.php
Création : 5 novembre 2013
Dernière modification : 22 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de modifier le site d'un tableau.
Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/

require_once "class.tableaux.php";
$db=new db();
$db->select("pl_poste_tab","*","tableau='$tableauNumero'");
$site=$db->result[0]['site'];

echo <<<EOD
<form name='form' method='get'>
<input type='hidden' id='numero' value='$tableauNumero'/>
  <table><tr><td style='width:600px;'>
    <h3>Configuration du site</h3>
    </td><td style='style='text-align:right;'>
      <input type='button' value='Retour' class='ui-button retour'/>
      <input type='button' value='Valider' class='ui-button' onclick='tabSiteUpdate();' id='submitSite'/>
  </td></tr></table>

Affecter ce tableau au site :
<select id='selectSite'>
<option value=''>&nbsp;</option>
EOD;
for($i=1;$i<=$config['Multisites-nombre'];$i++){
  $selected=$i==$site?"selected='selected'":null;
  echo "<option value='$i' $selected >".$config["Multisites-site$i"]."</option>\n";
}
echo <<<EOD
</select>
</form>
EOD;
?>