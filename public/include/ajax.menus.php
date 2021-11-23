<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/ajax.menus.php
Création : 5 février 2017
Dernière modification : 7 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre la liste des groupes de postes et des étages dans la base de données
Appelé lors du clic sur le bouton "Enregistrer" de la dialog box "Liste des groupes" ou "Lsite des étages" à partir de la fiche poste

TODO: Les étages peuvent être supprimés s'ils sont attachés à des postes supprimés. TODO : permettre la restauration des étages lors de la restauration des postes (via restauration des tableaux).
TODO: Les groupes peuvent être supprimés s'ils sont attachés à des postes supprimés. TODO : permettre la restauration des groupes lors de la restauration des postes (via restauration des tableaux).
TODO: Faire en sorte de conserver les ID dans la base de données et façon à les utiliser comme clé.
*/

ini_set('display_errors', 0);

session_start();

include "config.php";
$CSRFToken=trim(filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING));
$menu = FILTER_INPUT(INPUT_POST, 'menu', FILTER_SANITIZE_STRING);
$option = FILTER_INPUT(INPUT_POST, 'option', FILTER_SANITIZE_STRING);
$tab = $_POST['tab'];

// New method to use for updating menus
if (in_array($menu, array('etages', 'groupes'))) {
    $tab = json_decode($tab);

    // Deleting items put in trash
    $ids = array();
    foreach ($tab as $elem) {
        $ids[] = $elem->id;
    }
    $ids = implode(',', $ids);

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("select_$menu", ['id' => "NOT IN$ids"]);


    // Adding new items
    $db_ids = array();
    $db=new db();
    $db->select("select_$menu");
    foreach ($db->result as $elem) {
        $db_ids[] = $elem['id'];
    }

    foreach ($tab as $elem) {
        if (!in_array($elem->id, $db_ids)) {
            $db = new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("select_$menu", ["valeur" => $elem->value, "rang" => $elem->place]);
        } else {
            $db = new db();
            $db->CSRFToken = $CSRFToken;
            $db->update("select_$menu", ["rang" => $elem->place], ["id" => $elem->id]);
        }
    }

    echo json_encode('ok');
    exit;
}

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("select_$menu");
foreach ($tab as $elem) {
    $elements = array("valeur"=>$elem[0],"rang"=>$elem[1]);
    if ($option == 'type') {
        $elements['type'] = $elem[2];
    }
    if ($option == 'categorie') {
        $elements['categorie'] = $elem[2];
    }

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("select_$menu", $elements);
}
echo json_encode('ok');
