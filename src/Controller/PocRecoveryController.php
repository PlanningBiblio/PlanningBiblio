<?php

namespace App\Controller;

use App\Controller\PocAbsenceController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocRecoveryController extends PocAbsenceController
{
    /**
     * @Route("/pocrecovery", name="poc.recovery", methods={"GET"})
     */
    public function index(Request $request)
    {
        $this->templateParams(array('mode' => $this->mode));
        return $this->output('poc/pocabsence.html.twig');
    }
}
