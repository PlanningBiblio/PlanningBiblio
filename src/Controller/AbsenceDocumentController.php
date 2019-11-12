<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class AbsenceDocumentController extends BaseController
{
    /**
     * @Route("/absences/document/{id}", name="absences.document.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $file = new File('/home/planningb/www/planningbiblio/upload/absences/subjects.csv');
        $response = new BinaryFileResponse($file);

        return $response;
    }
}
