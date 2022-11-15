<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\Routing\Annotation\Route;

class HelpController extends BaseController
{
    /**
     * @Route("/help", name="help", defaults={"no-csrf": 1})
     */
    public function index()
    {
        return $this->output('help/index.html.twig');
    }
}
