<?php

namespace App\Controller;

use App\Controller\PocAbsenceController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocHolidayController extends PocAbsenceController
{
    use \PlanningBiblio\Traits\HolidayTest;
    /**
     * @Route("/pocholiday", name="poc.holiday", methods={"GET"})
     */
    public function index(Request $request)
    {
        #$this->templateParams(array('mode' => $this->mode));

        $data = $this->prepareAdd($request);
        $data = $this->halfday($data);
        $this->templateParams(array('addData' => $data));
        return $this->output('poc/pocconges.html.twig');

    }

    protected function prepareAdd(Request $request) {
        // Traitements dans la classe mère Absence
        $result = parent::prepareAdd($request);
        // Traitements spécifiques
        $result .= "\nTraitements spécifiques";
        // Fin
        return $result;
    }

}

//+---------------------+-------------+------+-----+---------------------+----------------+
//| Field               | Type        | Null | Key | Default             | Extra          |
//+---------------------+-------------+------+-----+---------------------+----------------+
//| id                  | int(11)     | NO   | PRI | NULL                | auto_increment |
//| perso_id            | int(11)     | NO   |     | NULL                |                |
//| debut               | datetime    | NO   |     | NULL                |                |
//| fin                 | datetime    | NO   |     | NULL                |                |
//| halfday             | tinyint(4)  | YES  |     | 0                   |                |
//| start_halfday       | varchar(20) | YES  |     |                     |                |
//| end_halfday         | varchar(20) | YES  |     |                     |                |
//| commentaires        | text        | YES  |     | NULL                |                |
//| refus               | text        | YES  |     | NULL                |                |
//| heures              | varchar(20) | YES  |     | NULL                |                |
//| debit               | varchar(20) | YES  |     | NULL                |                |
//| saisie              | timestamp   | NO   |     | CURRENT_TIMESTAMP   |                |
//| saisie_par          | int(11)     | NO   |     | NULL                |                |
//| modif               | int(11)     | NO   |     | 0                   |                |
//| modification        | timestamp   | NO   |     | 0000-00-00 00:00:00 |                |
//| valide_n1           | int(11)     | NO   |     | 0                   |                |
//| validation_n1       | timestamp   | NO   |     | 0000-00-00 00:00:00 |                |
//| valide              | int(11)     | NO   |     | 0                   |                |
//| validation          | timestamp   | NO   |     | 0000-00-00 00:00:00 |                |
//| solde_prec          | float       | YES  |     | NULL                |                |
//| solde_actuel        | float       | YES  |     | NULL                |                |
//| recup_prec          | float       | YES  |     | NULL                |                |
//| recup_actuel        | float       | YES  |     | NULL                |                |
//| reliquat_prec       | float       | YES  |     | NULL                |                |
//| reliquat_actuel     | float       | YES  |     | NULL                |                |
//| anticipation_prec   | float       | YES  |     | NULL                |                |
//| anticipation_actuel | float       | YES  |     | NULL                |                |
//| supprime            | int(11)     | NO   |     | 0                   |                |
//| suppr_date          | timestamp   | NO   |     | 0000-00-00 00:00:00 |                |
//| information         | int(11)     | NO   |     | 0                   |                |
//| info_date           | timestamp   | YES  |     | NULL                |                |
//+---------------------+-------------+------+-----+---------------------+----------------+
