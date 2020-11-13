<?php

namespace App\PlanningBiblio;

include_once(__DIR__.'/../../public/include/db.php');

class Migration
{
    //check vérifie s'il y a une différence entre les versions disponibles et les versions existant dans la base de données
    //si count est différent de 0, on renverra vers la page de maintenance
    public function check(){
        $data = new \db();
        $data->select2("doctrine_migration_versions", "version");
        $migrated_versions = array();
        $toAdd = 0;
        $toDelete = 0;
        $count = null;

        $migrations_available = glob('../src/Migrations/*.php');

        if(!empty($migrations_available)){
            foreach ($migrations_available as &$m){
                $m = "App\Migrations\\".substr($m,18,21);
            }
        }

        if($data->result){
            foreach ($data->result as $elem){
                $migrated_versions[] = $elem['version'];
            }
        }

        if(empty($migrated_versions) and !empty($migrations_available)){
            $toAdd+=count($migrations_available);
        } elseif (!empty($migrated_versions) and !empty($migrations_available)){
                foreach($migrated_versions as $mv){
                    if(!in_array($mv, $migrations_available)){
                        $toDelete++;
                    }
                }
                foreach($migrations_available as $ma){
                    if(!in_array($ma, $migrated_versions)){
                        $toAdd++;
                    }
                }
            }
        if ($toAdd == 0 and $toDelete == 0){
            $count = 0;
        } else {
            if ($toAdd == 0 or $toDelete > $toAdd){
                $count -= $toDelete;
            } else{
                $count += $toAdd;
            }
        }
        return $count;
    }

    //toUp renvoie la liste des migrations à exécuter
    public function toUp(){
        $data = new \db();
        $data->select2("doctrine_migration_versions", "version");
        $migrated_versions = array();
        $toDisplay = array();

        $migrations_available = glob('../src/Migrations/*.php');

        foreach ($migrations_available as &$m){
            $m = "App\Migrations\\".substr($m,18,21);
        }

        if($data->result){
            foreach ($data->result as $elem){
                $migrated_versions[] = $elem['version'];
            }
        }

        if(empty($migrated_versions) and !empty($migrations_available)){
            foreach($migrations_available as $ma){
                $toDisplay[]['version'] = $ma;
            }
        } elseif (!empty($migrated_versions) and !empty($migrations_available)){
            foreach($migrations_available as $ma){
                if(!in_array($ma, $migrated_versions)){
                    $toDisplay[]['version'] = $ma;
                }
            }
        }

        return $toDisplay;
    }

    //toDown() renvoie la liste des migrations à retirer
    public function toDown(){
        $data = new \db();
        $data->select2("doctrine_migration_versions", "version");
        $migrated_versions = array();
        $toDisplay = array();

        $migrations_available = glob('../src/Migrations/*.php');

        if(!empty($migrations_available)){
            foreach ($migrations_available as &$m){
                $m = "App\Migrations\\".substr($m,18,21);
            }
        }

        foreach ($data->result as $elem){
            $migrated_versions[] = $elem['version'];
        }

        if(!empty($migrated_versions) and !empty($migrations_available)){
            foreach($migrated_versions as $mv){
                if(!in_array($mv, $migrations_available)){
                    $toDisplay[]['version'] = $mv;
                }
            }
        } elseif (!empty($migrated_versions) and empty($migrations_available)){
            foreach($migrated_versions as $mv){
                $toDisplay[]['version'] = $mv;
            }
        }
        return $toDisplay;
    }

}
