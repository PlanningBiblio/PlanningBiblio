<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/menu.php
Création : mai 2011
Dernière modification : 27 juillet 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche le menu principal.
Le menu est affiché en HTML, les sous-menu s'affiche à l'aide des fonctions JavaScript

Cette page est appelée par le fichier index.php
*/

require_once("class.menu.php");

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
foreach ($keys as $key) {
    echo "zlien[$key] = new Array;\n";
    $keys2=array_keys($menu[$key]);
    sort($keys2);
    unset($keys2[0]);
    $i=0;
    foreach ($keys2 as $key2) {
        echo "zlien[$key][$i] = \"<a href='{$menu[$key][$key2]['url']}' class='ejsmenu'>{$menu[$key][$key2]['titre']}<\/a>\";\n";
        $i++;
    }
}

?>

function pop(msg,li){
  $(".menu_table").remove();

  posTop = li.offset().top+li.height()+2;
  posLeft = li.offset().left+li.width()/2-85;

  var content ="<table cellspacing='0' border='0' class='menu_table' style='top:"+posTop+"px; left:"+posLeft+"px;'>";

  for(i in msg){
    content += "<tr><td class='menu_td2' >"+msg[i]+"</td></tr>";
  }
  content += "</td></tr></table>";

  $("body").append(content);
}

$(document).click(function(){
  $(".menu_table").remove();
});
-->
</script>

<?php
echo "<div id='topgauche'>\n";
echo "<table cellpadding='0' cellspacing='0' border='0' style='width:100%;'><tr>\n";
echo "<td class='logo_td' rowspan='4'><font class='noprint'>\n";
echo "<a href='index.php'><div id='logo'></div></a>\n";
echo "</font></td></tr>\n";
echo "<tr><td>&nbsp;</td></tr>\n";
echo "<tr id='topmenu'>\n";

echo "<td style='text-align:center;' class='menu_td'>\n";
echo "<ul class='menu_ul'>\n";
foreach ($keys as $key) {
    echo "<li onmousemove='pop(zlien[$key],$(this))' class='menu_li'><a href='{$menu[$key][0]['url']}' class='ejsmenu2'>{$menu[$key][0]['titre']}</a></li>\n";
}
echo "</ul>\n";

echo "<td align='right'  ><font  class='noprint' style='font-size:19px'>\n";
echo $_SESSION['login_prenom']." ".$_SESSION['login_nom'];
echo "</font></td>\n";
echo "<td id='logout_img'>\n";
echo "<a href='authentification.php' title='Déconnexion' >\n";
echo "<span class='pl-icon pl-icon-logout'></span></a></td>\n";
echo "</tr>\n";
echo "<tr><td colspan='2' style='text-align:right;'>\n";

// Si le module PlanningHebdo ou ICS-Export sont activés, remplace "Changer le mot de passe" par "Mon Compte"
if ($config['PlanningHebdo'] or $config['ICS-Export']) {
    echo "<a href='{$config['URL']}/myaccount' class='myAccountLink'>\n";
    echo "Mon Compte</a>\n";
}
// Mot de passe modifiable seulement si authentification SQL
elseif ($_SESSION['oups']['Auth-Mode']=="SQL") {
    echo "<a href='index.php?page=personnel/password.php' class='myAccountLink'>\n";
    echo "Changer de mot de passe\n";
}
echo "<div id='logout_text'><a href='authentification.php' >Déconnexion</a></div>\n";
?>
</td>
</tr>
</table>
</div>
</div>
<iframe id='popup' style='display:none;' ></iframe>
<!--				Fin du menu			-->