<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/class.menu.inc
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions permettant de construire le menu principal.

Ce fichier est appelé par le fichier include/menu.php
*/

// pas de $version=acces direct au fichier => Accès refusé
if (!isset($version)) {
    include_once "accessDenied.php";
}

class menu
{
    public $elements=array();

    public function __construct()
    {
    }

    public function fetch()
    {
        $config = $GLOBALS['config'];

        $menu=array();
        $db=new db();
        $db->select("menu", null, null, "ORDER BY `niveau1`,`niveau2`");
        foreach ($db->result as $elem) {
            if ($elem['condition']) {
                if (substr($elem['condition'], 0, 7)=="config=") {
                    $tmp = substr($elem['condition'], 7);
                    $values = explode(";", $tmp);
                    foreach ($values as $value) {
                        if (empty($config[$value])) {
                            continue 2;
                        }
                    }
                }
            }
            $menu[$elem['niveau1']][$elem['niveau2']]['titre']=$elem['titre'];
            $menu[$elem['niveau1']][$elem['niveau2']]['url']=$elem['url'];
        }

        $sites = sites();

        if (!empty($sites)) {
            $i = 1;

            foreach ($sites as $site) {
                $menu[30][$i]['titre']=$site['name'];
                $menu[30][$i]['url']='planning/poste/index.php&amp;site='.$site['id'];
                $i++;
            }
        }

        $this->elements=$menu;
    }
}
