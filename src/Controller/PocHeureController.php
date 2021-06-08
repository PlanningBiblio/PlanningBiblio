<?php

namespace App\Controller;

use App\Controller\PocController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocHeureController extends PocController
{
    private $mode = 'heure';

    /**
     * @Route("/pocheure", name="poc.heure", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $this->templateParams(array('mode' => $this->mode));
        return $this->output('poc/pocheure.html.twig');
    }
}
