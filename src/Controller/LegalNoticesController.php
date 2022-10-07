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
    public function legalNotices(Request $request)
    {
        $legal_notices_gdpr = $this->config('legalNotices-GDPR');
        $this->templateParams(array("legal_notices_gdpr" => $legal_notices_gdpr));

        return $this->output('legalNotices/index.html.twig');
    }
}