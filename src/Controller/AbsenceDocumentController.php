<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class AbsenceDocumentController extends BaseController
{
    #[Route(path: '/absences/document/{id}', name: 'absences.document.index', methods: ['GET'])]
    public function index(Request $request, Session $session): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $id = $request->get('id');
        $ad = $this->entityManager->getRepository(AbsenceDocument::class)->find($id);

        $absenceDocument = new AbsenceDocument();
        $file = new File($absenceDocument->upload_dir() . $ad->absence_id() . '/' . $ad->id() . '/' . $ad->filename());

        return new BinaryFileResponse($file);
    }

    #[Route(path: '/absences/document/{id}', name: 'absences.document.delete', methods: ['DELETE'])]
    public function delete(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');
        $ad = $this->entityManager->getRepository(AbsenceDocument::class)->find($id);
        $ad->deleteFile();
        $this->entityManager->remove($ad);
        $this->entityManager->flush();
        return new Response();
    }

   #[Route(path: '/absences/document/{id_absence}', name: 'absences.document.add', methods: ['POST'])]
    public function add(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id_absence');
        $file = $request->files->get('documentFile');
        if (!empty($file)) {
            $filename = $file->getClientOriginalName();
            $ad = new AbsenceDocument();
            $ad->absence_id($id);
            $ad->filename($filename);
            $ad->date(new \DateTime());
            $this->entityManager->persist($ad);
            $this->entityManager->flush();

            $absenceDocument = new AbsenceDocument();
            $file->move($absenceDocument->upload_dir() . $id . '/' . $ad->id(), $filename);
        }
        return new Response();
    }

   #[Route(path: '/absences/documents/{id_absence}', name: 'absences.document.list', methods: ['GET'])]
    public function list(Request $request, Session $session): \Symfony\Component\HttpFoundation\Response
    {
        $id = $request->get('id_absence');
        $absdocs = $this->entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);
        $adarray = array();
        foreach ($absdocs as $absdoc) {
            $adarray[] = array('filename' => $absdoc->filename(), 'id' => $absdoc->id());
        }
        $response = new Response();
        $response->setContent(json_encode($adarray));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
