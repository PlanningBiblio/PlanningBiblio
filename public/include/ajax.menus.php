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
$db=new db();
$db->CSRFToken = $CSRFToken;
if($menu != 'services'){
    $db->delete("select_$menu");
    foreach ($tab as $elem) {
        if (!in_array($menu, array('etages', 'groupes'))) {
            $elem[0] = htmlentities($elem[0], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
        }
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
} else {
    $db2 = new db();
    $db2->select("select_$menu");
    $res = array();
    if ($db2->result){
        foreach($db2->result as $elem){
            $res[$elem['id']] = $elem['valeur'];
        }
    }

    $valeurs_liste = array();
    foreach($tab as $elem){
        $valeurs_liste[] = $elem[0];
    }

    foreach($db2->result as $elem){
        if(!in_array($elem['valeur'], $valeurs_liste)){
            $db->delete("select_$menu", array("id" => $elem['id']));
        }
    }
    foreach($tab as $elem){
        $elements = array("valeur"=>$elem[0],"rang"=>$elem[1]);
        $id = array_search($elements['valeur'], $res) ?? null;
        if ($id){ // Si l'on a déjà la valeur dans select_services = modification du rang
            $db->update("select_$menu", $elements, array("id" => $id));
        } else {
            $db->insert("select_$menu", $elements);
        }
    }
    $db2 = new db();
    $db2->select("select_$menu");
    $options = array();
    if ($db2->result){
        foreach($db2->result as $elem){
            $options[$elem['valeur']] = $elem['id'];
        }
    }
    echo json_encode($options);

}
