<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/class.menu.inc
Création : 22 juillet 2013
Dernière modification : 22 juin 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier regroupant les fonctions permettant de construire le menu principal.

Ce fichier est appelé par le fichier include/menu.php
*/

// pas de $version=acces direct au fichier => Accès refusé
$version = $GLOBALS['version'] ?? null;

if (!isset($version)) {
    include_once __DIR__."/accessDenied.php";
}

class menu
{
    public $elements=array();

    public function __construct()
    {
    }

    public function fetch()
    {
        $menu=array();
        $db=new db();
        $db->select("menu", null, null, "ORDER BY `niveau1`,`niveau2`");
        foreach ($db->result as $elem) {
            if ($elem['condition']) {
                if (substr($elem['condition'], 0, 7)=="config=") {
                    $tmp = substr($elem['condition'], 7);
                    $values = explode(";", $tmp);
                    foreach ($values as $value) {
                        if (empty($GLOBALS['config'][$value])) {
                            continue 2;
                        }
                    }
                }
            }
            if (substr($elem['url'], 0, 1) == '/') {
                $url = substr($elem['url'], 1);
            } else {
                $url = 'index.php?page=' . $elem['url'];
            }
            $menu[$elem['niveau1']][$elem['niveau2']]['titre']=$elem['titre'];
            $menu[$elem['niveau1']][$elem['niveau2']]['url'] = $url;
        }

        if ($GLOBALS['config']['Multisites-nombre']>1) {
            for ($i=0;$i<$GLOBALS['config']['Multisites-nombre'];$i++) {
                $j=$i+1;
                $menu[30][$j]['titre']=$GLOBALS['config']["Multisites-site".$j];
                $menu[30][$j]['url']="index.php?page=planning/poste/index.php&amp;site=$j";
            }
        }

        $this->elements=$menu;
    }
}
