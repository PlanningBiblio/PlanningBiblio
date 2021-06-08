<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocJourController extends BaseController
{
    /**
     * @Route("/pocjour", name="poc.jour", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $this->templateParams(array('mode' => 'jour'));
        return $this->output('poc/pocjour.html.twig');
    }
}
