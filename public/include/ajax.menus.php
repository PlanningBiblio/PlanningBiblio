<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/ajax.menus.php
Création : 5 février 2017
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

// New process
if (in_array($menu, array('services', 'statuts'))) {

    $insert = array();
    $update = array();
    $update_ids = array();

    switch($menu) {
        case 'services' :
            $index = 2;
            break;
        case 'statuts' :
            $index = 3;
            break;
    }

    foreach ($tab as $elem) {
        if ( is_numeric($elem[$index])) {
            $update[] = $elem;
            $update_ids[] = $elem[$index];
        } elseif (substr($elem[$index], 0, 4) == 'new_') {
            $insert[] = $elem;
        }
    }

    // Delete removed items
    $db = new db();
    $db->select('select_' . $menu);
    if ($db->result) {
        $db2 = new dbh();
        $db2->CSRFToken = $CSRFToken;
        $db2->prepare("DELETE FROM `select_$menu` WHERE `id` = :id;");
        foreach ($db->result as $elem) {
            if (!in_array($elem['id'], $update_ids)) {
                $db2->execute(array(':id' => $elem['id']));
            }
        }
    }

    // Update changed items
    $db = new dbh();
    $db->CSRFToken = $CSRFToken;
    switch($menu) {
        case 'services' :
            $db->prepare("UPDATE `select_$menu` SET `valeur` = :valeur, `rang` = :rang WHERE `id` = :id;");
            break;
        case 'statuts' :
            $db->prepare("UPDATE `select_$menu` SET `valeur` = :valeur, `categorie` = :categorie, `rang` = :rang WHERE `id` = :id;");
            break;
    }

    foreach ($update as $elem) {
        switch($menu) {
            case 'services' :
                $db->execute(array(':valeur' => $elem[0], ':rang' => $elem[1], ':id' => $elem[2]));
                break;
            case 'statuts' :
                $db->execute(array(':valeur' => $elem[0], ':rang' => $elem[1], ':categorie' => $elem[2], ':id' => $elem[3]));
                break;
        }
    }

    // Add new items
    $db = new dbh();
    $db->CSRFToken = $CSRFToken;
    switch($menu) {
        case 'services' :
            $db->prepare("INSERT INTO `select_$menu` (`valeur`, `rang`) VALUES (:valeur, :rang);");
            break;
        case 'statuts' :
            $db->prepare("INSERT INTO `select_$menu` (`valeur`, `rang`, `categorie`) VALUES (:valeur, :rang, :categorie);");
            break;
    }

    foreach ($insert as $elem) {
        switch($menu) {
            case 'services' :
                $db->execute(array(':valeur' => $elem[0], ':rang' => $elem[1]));
                break;
            case 'statuts' :
                $db->execute(array(':valeur' => $elem[0], ':rang' => $elem[1], ':categorie' => $elem[2]));
                break;
        }
    }

    // Select items from DB
    $db = new db();
    $db->select("select_$menu", null, null, "ORDER BY `rang`");
    $items = json_encode($db->result);

    // Select used items
    $used = array();

    $field = substr($menu, 0, -1);
    $db = new db();
    $db->select("personnel", $field, null, "GROUP BY `$field`");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $used[] = $elem[$field];
        }
    }
    $used = json_encode($used);

    echo json_encode( [$items, $used] );
    exit;
}

// Old process

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("select_$menu");
foreach ($tab as $elem) {
    $elem[0] = htmlentities($elem[0], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
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
