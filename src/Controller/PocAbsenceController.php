<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocAbsenceController extends BaseController
{
    protected $mode = '';

    /**
     * @Route("/pocabsence", name="poc.absence", methods={"GET"})
     */
    public function index(Request $request)
    {
        #$this->mode = $request->get('mode') ?? 'jour';

        $data = $this->prepareAdd($request);
        $this->templateParams(array('addData' => $data));
        return $this->output('poc/pocabsence.html.twig');
    }

    protected function prepareAdd(Request $request)
    {
        // Si on ajoute une absence
        // Traitements
        // Fin

        // Si on ajoute un congé
        // Traitements
        // Traitements dans la classe fille
        // Fin

        $result = "Traitements génériques";
        return $result;
    }
}

//+---------------+--------------+------+-----+---------------------+----------------+
//| Field         | Type         | Null | Key | Default             | Extra          |
//+---------------+--------------+------+-----+---------------------+----------------+
//| id            | int(11)      | NO   | PRI | NULL                | auto_increment |
//| perso_id      | int(11)      | NO   | MUL | 0                   |                |
//| debut         | datetime     | NO   | MUL | NULL                |                |
//| fin           | datetime     | NO   | MUL | NULL                |                |
//| motif         | text         | NO   |     | NULL                |                |
//| motif_autre   | text         | NO   |     | NULL                |                |
//| commentaires  | text         | NO   |     | NULL                |                |
//| etat          | text         | NO   |     | NULL                |                |
//| demande       | datetime     | NO   |     | NULL                |                |
//| valide        | int(11)      | NO   |     | 0                   |                |
//| validation    | timestamp    | NO   |     | 0000-00-00 00:00:00 |                |
//| valide_n1     | int(11)      | NO   |     | 0                   |                |
//| validation_n1 | timestamp    | NO   |     | 0000-00-00 00:00:00 |                |
//| pj1           | int(1)       | YES  |     | 0                   |                |
//| pj2           | int(1)       | YES  |     | 0                   |                |
//| so            | int(1)       | YES  |     | 0                   |                |
//| groupe        | varchar(14)  | YES  | MUL | NULL                |                |
//| cal_name      | varchar(300) | NO   | MUL | NULL                |                |
//| ical_key      | text         | NO   |     | NULL                |                |
//| last_modified | varchar(255) | YES  |     | NULL                |                |
//| uid           | text         | YES  |     | NULL                |                |
//| rrule         | text         | YES  |     | NULL                |                |
//| id_origin     | int(11)      | NO   |     | 0                   |                |
//+---------------+--------------+------+-----+---------------------+----------------+


