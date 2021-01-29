<?php

namespace App\PlanningBiblio;

class Utils
{

    public static function agentName($name, $surname, $format){
        $option = null;

        if($format == "full"){
            $option = $GLOBALS['config']['Agent-FullNameFormat'];
        }

        if($option == "Name Surname"){
            return $name.' '.$surname;
        }

        if($option == "Surname Name"){
            return $surname.' '.$name;
        }

        if($format == "short"){
            $option = $GLOBALS['config']['Agent-NameFormat'];
        }

        if($option == "N. Surname"){
            return $name[0].'. '.$surname;
        }

        if($option == "Name S."){
            return $name.' '.$surname[0].'.';
        }

        if($option == "Surname N."){
            return $surname.' '.$name[0].'.';
        }

        if($option == "S. Name"){
            return $surname[0].'. '.$name;
        }

        if($option == "Name"){
            return $name;
        }

        if($option == "Surname"){
            return $surname;
        }


    }
}
