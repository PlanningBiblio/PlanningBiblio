<?php
/*
Planning Biblio, Version 1.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : include/menu.php
Création : mai 2011
Dernière modification : 17 décembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le menu principal.
Le menu est affiché en HTML, les sous-menu s'affiche à l'aide des fonctions JavaScript

Cette page est appelée par le fichier index.php
*/

include "class.menu.php";

$m=new menu();
$m->fetch();
$menu=$m->elements;

?>
<!--				Début du menu			-->
<div class="navigation">
<div class='popper' id='topdeck'></div>

<script type="text/JavaScript">
<!--
zlien = new Array;
<?php
$keys=array_keys($menu);
sort($keys);
foreach($keys as $key){
  echo "zlien[$key] = new Array;\n";
  $keys2=array_keys($menu[$key]);
  sort($keys2);
  unset($keys2[0]);
  $i=0;
  foreach($keys2 as $key2){
    echo "zlien[$key][$i] = \"<a href='index.php?page={$menu[$key][$key2]['url']}' class='ejsmenu'>{$menu[$key][$key2]['titre']}<\/a>\";\n";
    $i++;
  }
}
$colspan=count($keys)+1;

?>

var nava = (document.layers);
var dom = (document.getElementById);
var iex = (document.all);
if (nava) { skn = document.topdeck }
else if (dom) { skn = document.getElementById("topdeck").style }
else if (iex) { skn = topdeck.style }

function pop(msg,td){
  a=true;
  skn.visibility = "hidden";

  posLeft = td.offset().left+td.width()/2-100;
  skn.left=posLeft+"px";

  posTop = td.offset().top+td.height()+2;
  skn.top=posTop+"px";

  var content ="<table border='0' cellpadding='0' cellspacing='0' style='background:#000000;width:200px:'><tr><td>";
  content =content+"<table style='width:100%;min-width:200px;' border='0' cellpadding='0' cellspacing='1'>";
  pass = 0
  while (pass < msg.length){
    content += "<tr><td class='menu_td2' ><font size='1' face=\"verdana\">&nbsp;&nbsp;"+msg[pass]+"<\/font><\/td><\/tr>";
    pass++;
  }
  content += "<\/table><\/td><\/tr><\/table>";
  if (nava){
    skn.document.write(content);
    skn.document.close();
    skn.visibility = "visible";
  }
    else if (dom){
      document.getElementById("topdeck").innerHTML = content;
      skn.visibility = "visible";
  }
    else if (iex){
      document.all("topdeck").innerHTML = content;
      skn.visibility = "visible";
  }
}

function kill(){
  skn.visibility = "hidden";
}

document.onclick = kill;
-->
</script>

<?php
echo "<div id='topgauche'>\n";
echo "<table cellpadding='0' cellspacing='0' border='0' style='width:100%;'><tr>\n";
echo "<td style='width:340px;text-align:left' rowspan='4'><font class='noprint'>\n";
echo "<a href='index.php'><img src='img/logo.png' alt='Logo' id='logo' style='width:160px;margin-top:7px;' border='0'/></a>\n";
echo "</font></td></tr>\n";
echo "<tr><td>&nbsp;</td></tr>\n";
echo "<tr id='topmenu'>\n";
foreach($keys as $key){
  echo "<td style='text-align:center;' onmousemove='pop(zlien[$key],$(this))' class='menu_td'><a href='index.php?page={$menu[$key][0]['url']}' class='ejsmenu2'>{$menu[$key][0]['titre']}</a></td>\n";
}
echo "<td align='right'  ><font  class='noprint' style='font-size:19px'>\n";
echo $_SESSION['login_prenom']." ".$_SESSION['login_nom'];
echo "</font></td>\n";
echo "<td id='logout_img'>\n";
echo "<a href='authentification.php' title='Déconnexion' >\n";
echo "<img src='img/loggoff.png' border='0' style='width:16px;'/></a></td>\n";
echo "</tr>\n";
echo "<tr><td colspan='$colspan' style='text-align:right;'>\n";

// Si plugin PlanningHebdo, remplace "Changer le mot de passe" par "Mon Compte"
if(in_array("planningHebdo",$plugins)){
  echo "<a href='index.php?page=plugins/planningHebdo/monCompte.php' style='font-size:9pt;'>\n";
  echo "Mon Compte</a>\n";
}
// Mot de passe modifiable seulement si authentification SQL
elseif($_SESSION['oups']['Auth-Mode']=="SQL"){
  echo "<a href='index.php?page=personnel/password.php' style='font-size:9pt;'>\n";
  echo "Changer de mot de passe\n";
}
echo "<div id='logout_text'><a href='authentification.php' >Déconnexion</a></div>\n";
?>
</td>
</tr>
</table>
</div>
</div>
<div class='noprint'>
<br/>
<br/>
</div> <!-- noprint -->
<iframe id='popup' style='display:none;' ></iframe>
<!--				Fin du menu			-->