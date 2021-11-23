<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : postes/class.postes.php
Création : 29 novembre 2012
Dernière modification : 22 juin 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe postes contenant la fonction postes::fetch permettant de rechercher les postes dans la base de données

Utilisée par les fichiers du dossier "postes"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé

$version = $GLOBALS['version'] ?? null;
if (!isset($version)) {
    include_once (__DIR__."/../include/accessDenied.php");
}

class postes
{
    public $CSRFToken = null;
    public $id=null;
    public $site=null;

    public function __construct()
    {
    }

    public function delete()
    {
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("postes", array("supprime"=>"SYSDATE"), array("id"=>$this->id));
    }

    public function fetch($sort="nom", $name=null, $group=null)
    {
        // Floors
        $floors = array();
        $db=new db();
        $db->sanitize_string = false;
        $db->select("select_etages");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $floors[$elem['id']] = $elem['valeur'];
            }
        }

        $where=array("supprime"=>null);
    
        if ($this->site) {
            $where["site"]=$this->site;
        }

        //	Select All
        $db=new db();
        $db->select2("postes", null, $where, "ORDER BY $sort");
    

        $all=array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $all[$elem['id']]=$elem;
                $all[$elem['id']]['etage'] = $floors[$elem['etage']];
            }
        }

        //	By default $result=$all
        $result=$all;

        //	If name, keep only matching results
        if (!empty($all) and $name) {
            $result=array();
            foreach ($all as $elem) {
                if (pl_stristr($elem['nom'], $name)) {
                    $result[$elem['id']]=$elem;
                }
            }
        }

        $this->elements=$result;
    }
}
