<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HelpController extends Controller
{
    /**
     * @Route("/help", name="help")
     */
    public function index()
    {

        $templates_params = array_merge($GLOBALS['templates_params'], ['controller_name' => 'HelpController']);
        
        return $this->render('help/index.html.twig', $templates_params);
    }
}
