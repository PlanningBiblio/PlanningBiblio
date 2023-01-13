<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : activites/class.activites.php
Création : mai 2011
Dernière modification : 22 juin 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe activites : contient les fonctions de recherches des activites
Page appelée par les pages du dossier activites
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
$version = $GLOBALS['version'] ?? null;

if (!isset($version) and php_sapi_name() != 'cli') {
    include_once "../include/accessDenied.php";
}

class activites
{
    public $id=null;
    public $elements=array();
    public $deleted=null;
    public $CSRFToken = null;

    public function __construct()
    {
    }

    public function delete()
    {
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("activites", array("supprime"=>"SYSDATE"), array("id"=>$this->id));
    }

    public function fetch()
    {
        $activites=array();
        $db=new db();
        if ($this->deleted) {
            $db->select2("activites");
        } else {
            $db->select2("activites", null, array("supprime"=>null));
        }
      
        if ($db->result) {
            $activites=$db->result;
        }
    
        usort($activites, "cmp_nom");
    
        $tmp=array();
        foreach ($activites as $elem) {
            $tmp[$elem['id']]=$elem;
        }
        $activites=$tmp;
        $this->elements=$activites;
    }
}
