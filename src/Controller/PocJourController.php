<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocJourController extends PocController
{
    private $mode = 'jour';

    /**
     * @Route("/pocjour", name="poc.jour", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $this->templateParams(array('mode' => $this->mode));
        return $this->output('poc/pocjour.html.twig');
    }
}
