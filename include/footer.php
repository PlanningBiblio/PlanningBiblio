<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/footer.php
Création : mai 2011
Dernière modification : 22 janvier 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affcihe le pied de page
Page notamment appelée par les fichiers index.php et admin/index.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "accessDenied.php";
  exit;
}
?>
</div> <!-- content or planningPoste -->
<div class='footer'>
PlanningBiblio (<?php echo $version; ?>) - Copyright &copy; 2011-2018 - J&eacute;r&ocirc;me Combes - 
<a href='http://www.planningbiblio.fr' target='_blank' style='font-size:9pt;'>www.planningbiblio.fr</a>
</div>
</body>
</html>

<?php
exit;
?>
