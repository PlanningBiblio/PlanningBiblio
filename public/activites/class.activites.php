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

}
