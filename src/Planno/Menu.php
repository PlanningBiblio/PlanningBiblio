<?php

namespace App\Planno;

class Menu
{
    public $elements = array();

    public function checkCondition($allConditions) {

        if ($allConditions != null && $allConditions != '') {

            $conditionsArray = explode('&', $allConditions);
            foreach ($conditionsArray as $condition) {
                if (substr($condition, 0, 7)=="config=") {
                    $tmp = substr($condition, 7);
                    $values = explode(";", $tmp);
                    foreach ($values as $value) {
                        if (empty($GLOBALS['config'][$value])) {
                            return false;
                        }
                    }
                } elseif (substr($condition, 0, 8)=="config!=") {
                    $tmp = substr($condition, 8);
                    $values = explode(";", $tmp);
                    foreach ($values as $value) {
                        if (!empty($GLOBALS['config'][$value])) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    public function fetch()
    {
        $menu = array();
        $db = new \db();
        $db->select("menu", null, null, "ORDER BY `niveau1`,`niveau2`");
        foreach ($db->result as $elem) {

            if ($this->checkCondition($elem['condition'])) {
                if (substr($elem['url'], 0, 1) == '/') {
                    $url = substr($elem['url'], 1);
                } else {
                    $url = 'index.php?page=' . $elem['url'];
                }
                $menu[$elem['niveau1']][$elem['niveau2']]['titre']=$elem['titre'];
                $menu[$elem['niveau1']][$elem['niveau2']]['url'] = $url;
            }
        }

        if ($GLOBALS['config']['Multisites-nombre']>1) {
            for ($i=0;$i<$GLOBALS['config']['Multisites-nombre'];$i++) {
                $j=$i+1;
                $menu[30][$j]['titre']=$GLOBALS['config']["Multisites-site".$j];
                $menu[30][$j]['url'] = $j;
            }
        }

        $this->elements=$menu;
    }

    public function get()
    {
        $this->fetch();
        $elements = $this->elements;
        
        $menu_entries = array();
        $menu_js = array();
        
        $keys = array_keys($elements);
        sort($keys);
        
        foreach ($keys as $key) {
            $menu_entries[] = array(
                'key' => $key,
                'url' => $elements[$key][0]['url'],
                'title' => $elements[$key][0]['titre']
                );
        
            $menu_js[$key] = array(
                'key' => $key,
                'items' => array()
                );
        
            $keys2 = array_keys($elements[$key]);
            sort($keys2);
            unset($keys2[0]);
        
            $i=0;
            foreach ($keys2 as $key2) {
                $menu_js[$key]['items'][$i] = array(
                    'key' => $key,
                    'url' => $elements[$key][$key2]['url'],
                    'title' => $elements[$key][$key2]['titre']
                    );
                $i++;
            }
        }

        return array(
            'menu_entries' => $menu_entries,
            'menu_js' => $menu_js,
            );
    }
}
