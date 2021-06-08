<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PocController extends BaseController
{
    /**
     * @Route("/poc", name="poc", methods={"GET"})
     */
    public function index(Request $request)
    {
        $mode = $request->get('mode') ?? 'jour';
        $controller = $this->getController($mode);

        $response = $this->forward("$controller::indexAction");

        return $response;
    }

    private function getController($mode)
    {
        $controller = '';
        switch ($mode) {
            case 'jour':
                $controller = 'App\Controller\PocJourController';
                break;
            case 'heure':
                $controller = 'App\Controller\PocHeureController';
                break;
        }

        return $controller;
    }
}
