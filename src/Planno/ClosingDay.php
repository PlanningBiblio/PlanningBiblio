<?php

namespace App\Planno;

require_once(__DIR__ . "/../../legacy/Common/feries.php");

class ClosingDay
{
    public $annee;
    public $debut;
    public $fin;
    public $auto;
    public $elements=array();
    public $error=false;
    public $index;
    public $CSRFToken;

    public function __construct()
    {
    }

    public function fetchByDate($date): void
    {
        // Recherche du jour férié correspondant à la date $date
        $tab=array();
        $db=new \db();
        $date = $db->escapeString($date);
        $db->select("jours_feries", "*", "jour='$date'");
        if ($db->result) {
            $tab=$db->result;
        }
        $this->elements=$tab;
    }
}
