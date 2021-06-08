<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocHeureController extends BaseController
{
    /**
     * @Route("/pocheure", name="poc.heure", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $this->templateParams(array('mode' => 'heure'));
        return $this->output('poc/pocheure.html.twig');
    }
}
