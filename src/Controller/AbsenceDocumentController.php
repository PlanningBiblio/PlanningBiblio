<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;

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
        $id = $request->get('id');
        $ad = $this->entityManager->getRepository(AbsenceDocument::class)->find($id);
        $file = new File(__DIR__ . AbsenceDocument::UPLOAD_DIR . $ad->absence_id() . '/' . $ad->filename());
        $response = new BinaryFileResponse($file);

        return $response;
    }
}
