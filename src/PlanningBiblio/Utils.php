<?php

namespace App\PlanningBiblio;

class Utils
{
    private $reason;

    public function getReason(){
        return $this->reason;
    }

    public function setReason($reason){
        $this->reason = $reason;
    }

    public function checkStrength($password) {
        $number = preg_match('@[0-9]@', $password);
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(strlen($password) < 8){
            $this->setReason("length");
        } else if(!$number || !$uppercase || !$lowercase || !$specialChars) {
            $this->setReason("character");
        } else {
            $this->setReason("OK");
        }
    }
}