<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/include/footer.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affcihe le pied de page
Page notamment appelée par les fichiers index.php et admin/index.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "accessDenied.php";
    exit;
}
?>
</div> <!-- content or planningPoste -->
<div class='footer'>
Planno (<?php echo $GLOBALS['displayed_version']; ?>) - 
<a href='https://www.planno.fr' target='_blank' style='font-size:9pt;'>www.planno.fr</a>
<!-- FIXME : ceci ne devrait pas être dans le footer -->
<script type='text/JavaScript' src='js/bootstrap-5.1.3-dist/bootstrap.js'></script>
</div>
</body>
</html>

<?php
exit;
?>
