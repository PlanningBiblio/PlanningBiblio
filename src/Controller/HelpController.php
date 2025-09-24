<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\Routing\Annotation\Route;

class HelpController extends BaseController
{
    #[Route(path: '/help', name: 'help')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->output('help/index.html.twig');
    }
}
