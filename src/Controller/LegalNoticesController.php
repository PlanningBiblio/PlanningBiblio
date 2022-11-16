<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalNoticesController extends BaseController
{

    /**
     * @Route("/legalNotices", name="legalnotices", methods={"GET"})
     */
    public function index(Request $request)
    {
        return $this->output('legalNotices/index.html.twig');
    }
}
