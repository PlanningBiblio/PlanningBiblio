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
<nav class="navigation navbar navbar-expand-custom">

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
echo "<div class='container-fluid'>\n";

echo "<button class='navbar-toggler ms-auto' type='button' data-bs-toggle='collapse' data-bs-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>\n";
echo "<div class='logo-favicon'></div></button>\n";

echo "<a class='navbar-brand collapse navbar-collapse' href='index.php'><div id='logo'></div></a>\n";
echo "<div class='collapse navbar-collapse text-right' id='navbarSupportedContent'>\n";
echo "<ul class='menu_ul navbar-nav me-auto mb-2 mb-lg-0'>\n";

foreach ($keys as $key) {
    $active = (in_array($page, ['planning/volants/index.php']) and $menu[$key][0]['titre'] == 'Planning') ? 'active' : null;
    $active = (in_array($page, ['conges/recup_pose.php', 'conges/infos.php', 'conges/credits.php']) and $menu[$key][0]['titre'] == 'Congés') ? 'active' : $active;
    $active = (strstr($page, 'statistiques') and $menu[$key][0]['titre'] == 'Statistiques') ? 'active' : $active;
    echo "<li onmousemove='pop(zlien[$key],$(this))' class='av-item dropdown menu_li'><a href='{$menu[$key][0]['url']}' class='ejsmenu2 $active dropdown-toggle'>{$menu[$key][0]['titre']}</a></li>\n";
}
echo "</ul>\n";

echo "<ul align='right'><font id='username' class='noprint' style='font-size:19px'>\n";
echo $_SESSION['login_prenom']." ".$_SESSION['login_nom'];
echo "</font></ul>\n";
echo "<div id='div_account'><ul colspan='2' style='text-align:right;'>\n";
echo "<ul id='logout_img'>\n";
echo "<a href='{$config['URL']}/logout' title='Déconnexion'>\n";
echo "<span class='pl-icon pl-icon-logout'></span></a></ul>\n";

// Si le module PlanningHebdo ou ICS-Export sont activés, remplace "Changer le mot de passe" par "Mon Compte"
if ($config['PlanningHebdo'] or $config['ICS-Export']) {
    echo "<a href='{$config['URL']}/myaccount' class='myAccountLink'>\n";
    echo "Mon Compte</a>\n";
}
// Mot de passe modifiable seulement si authentification SQL
elseif ($_SESSION['oups']['Auth-Mode']=="SQL") {
    echo "<a href='{$config['URL']}/agent/password' class='myAccountLink'>\n";
    echo "Changer de mot de passe\n";
}

?>
</ul>
</div>
<?php
echo "<div id='div_account_reverse'><ul class='menu_ul navbar-nav me-auto mb-2 mb-lg-0 colspan='2' style='text-align:right;'><li class='av-item dropdown menu_li'>\n";

// Si le module PlanningHebdo ou ICS-Export sont activés, remplace "Changer le mot de passe" par "Mon Compte"
if ($config['PlanningHebdo'] or $config['ICS-Export']) {
    echo "<a href='{$config['URL']}/myaccount' class='ejsmenu2'>\n";
    echo "Mon Compte</a>\n";
}
// Mot de passe modifiable seulement si authentification SQL
elseif ($_SESSION['oups']['Auth-Mode']=="SQL") {
    echo "<a href='index.php?page=personnel/password.php' class='ejsmenu2'>\n";
    echo "Changer de mot de passe\n";
}
echo"</a></li>\n";
echo "<li class='av-item dropdown menu_li'>\n";
echo "<a href='{$config['URL']}/logout' class='ejsmenu2'>\n";
echo "Deconnexion</a></li>\n";

?>
</ul>
</div>
</div>
</div>
</nav>
<iframe id='popup' style='display:none;' ></iframe>
<!--				Fin du menu			-->
