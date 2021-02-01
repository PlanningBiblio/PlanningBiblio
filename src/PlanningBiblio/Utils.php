<?php

namespace App\PlanningBiblio;

class Utils
{
    public static function checkStrength($password) {
        $number = preg_match('@[0-9]@', $password);
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(strlen($password) < 8){
            return json_encode("length");
        } else if(!$number || !$uppercase || !$lowercase || !$specialChars) {
            return json_encode("character");
        } else {
           return json_encode("OK");
        }
    }
}