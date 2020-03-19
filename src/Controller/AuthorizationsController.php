<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorizationsController extends BaseController
{

    /**
     * @Route("/access-denied", name="access-denied", methods={"GET"})
     */
    public function denied(Request $request)
    {
        return $this->output('access-denied.html.twig');
    }
}