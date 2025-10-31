<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Cron;

final class CrontabController extends BaseController
{
    #[Route('/crontab', name: 'crontab.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Temporary folder
        $tmp_dir=sys_get_temp_dir();

        $crons = $this->entityManager->getRepository(Cron::class)
            ->findAll();

        $elements = array();
        foreach ($crons as $cron) {

            if ($cron->isDisabled() ) {
                continue;
            }
            $elem = array(
                'id'             => $cron->getId(),
                'minute'         => $cron->getM(),
                'hour'           => $cron->getH(),
                'day_of_month'   => $cron->getDom(),
                'month'          => $cron->getMon(),
                'day_of_week'    => $cron->getDow(),
                'command'        => $cron->getCommand(),
                'comment'        => $cron->getComment(),
                'disabled'       => (int)$cron->isDisabled(),
                'last'           => $cron->getLast()
            );
            $elements[] = $elem;
        }

        $this->templateParams(array(
            'elements'  => $elements,
            'error'     => $request->query->get('error'),
            'post'      => $request->query->get('post'),
            'warning'   => $request->query->get('warning')
        ));

        return $this->output('crontab/index.html.twig');
    }

    #[Route(path: '/crontab', name: 'crontab.update', methods: ["POST"])]
    public function update(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $params = $request->request->all();

        if (empty($params)) {
            $error = "La modification de la Ordonnanceur est vide.";
        }
        else {

            $crons = $this->entityManager->getRepository(Cron::class)->findAll();

            foreach ($crons as $cron) {
                $cmd = $cron->getCommand();
                $isEnabled = array_key_exists($cmd, $params);

                try {
                    $cron->setDisabled($isEnabled ? 0 : 1);
                    //$this->entityManager->persist($cron);
                }
                catch (Exception $e) {
                    $error = 'Une erreur est survenue pendant la modification de la crontab !';
                }
            }
            $this->entityManager->flush();

        }

        if (isset($error)) {
            $session->getFlashBag()->add('error', $error);
        } else {
            $flash = 'La crontab a été modifiée avec succès';
            $session->getFlashBag()->add('notice', $flash);
        }

        return $this->redirectToRoute('crontab.index');
    }

    #[Route(path: '/crontab/add', name: 'crontab.add', methods: ['GET'])]
    public function add(Request $request)
    {
        $crons = $this->entityManager->getRepository(Cron::class)
            ->findAll();
        $crons_enabled = $this->entityManager->getRepository(Cron::class)
            ->findBy(['disabled' => 0]);

        foreach( $crons_enabled AS $c) {
            $command_id[] = $c->getId();
        }
        $this->templateParams(array(
            'id'    => null,
            'm'    => null,
            'h' => null,
            'command'=> null,
            'dom'   => null,
            'mon'  => null,
            'dow'  => null,
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'all_commands' => $crons,
            'command_ids'=> $command_id
        ));

        return $this->output('crontab/edit.html.twig');
    }

    #[Route(path: '/crontab/{id}', name: 'crontab.edit', methods: ['GET'])]
    public function edit(Request $request)
    {
        $crons = $this->entityManager->getRepository(Cron::class)
        ->findAll();
        $id = $request->get('id');
        
        $cron = $this->entityManager->getRepository(Cron::class)->findOneById($id);

        $command_id = [];
        $crons_enabled = $this->entityManager->getRepository(Cron::class)
            ->findBy(['disabled' => 0]);

        foreach( $crons_enabled AS $c) {
            $command_id[] = $c->getId();
        }

        $this->templateParams(array(
            'id'    => $id,
            'command'=> $cron->getCommand(),
            'comment'=> $cron->getComment(),
            'm'     => $cron->getM(),
            'h'     => $cron->getH(),
            'dom'   => $cron->getDom(),
            'mon'   => $cron->getMon(),
            'dow'   => $cron->getDow(),
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'all_commands' => $crons,
            'command_ids'=> $command_id
        ));

        return $this->output('crontab/edit.html.twig');
    }

    

    #[Route(path: '/crontab', name: 'crontab.delete', methods: ['DELETE'])]
    public function delete(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent(json_encode('CSRF error'));

            return $response;
        }

        $id = $request->get('id');

        $info = $this->entityManager->getRepository(Cron::class)->find($id);
        $this->entityManager->remove($info);
        $this->entityManager->flush();

        $flash = "Le command a bien été supprimée.";
        $session->getFlashBag()->add('notice', $flash);

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode('OK'));

        return $response;
    }

    private function frequence()
    {
        return;
    }
}
