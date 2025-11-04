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
                'last'           => $cron->getLast(),
                'resume'         => $this->resume($cron->getId())
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
        
        if (
            !isset($params['min']) || trim($params['min']) === '' ||
            !isset($params['hour']) || trim($params['hour']) === '' ||
            !isset($params['dom']) || trim($params['dom']) === '' ||
            !isset($params['mon']) || trim($params['mon']) === '' ||
            !isset($params['dow']) || trim($params['dow']) === ''
        ) {
            $error = "La modification de l'ordonnanceur est vide.";
        }

        else {
            $cron = $this->entityManager->getRepository(Cron::class)->findOneBy(["id"=> $params['id']]);
            try {
                $cron->setM($params['min']);
                $cron->setH($params['hour']);
                $cron->setDom($params['dom']);
                $cron->setMon($params['mon']);
                $cron->setDow($params['dow']);
                $cron->setDisabled(0);
            }
            catch (Exception $e) {
                $error = 'Une erreur est survenue pendant la modification de la crontab !';
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

    #[Route(path: '/crontab/info/{id}', name: 'crontab.info', methods: ['GET'])]
    public function info(int $id): Response
    {
        $cron = $this->entityManager->getRepository(Cron::class)->find($id);
        if (!$cron) {
            return new Response(json_encode(['error' => 'Not found']), 404, ['Content-Type' => 'application/json']);
        }

        $data = [
            'description' => $cron->getComment(),
            'm'   => $cron->getM(),
            'h'   => $cron->getH(),
            'dom' => $cron->getDom(),
            'mon' => $cron->getMon(),
            'dow' => $cron->getDow(),
        ];

        return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
    }

    #[Route(path: '/crontab/disabled', name: 'crontab.disabled', methods: ['POST'])]
    public function disabled(Request $request)
    {
        $id = $request->get('id');
        $checked = $request->get('checked');

        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        $cron = $this->entityManager->getRepository(Cron::class)->find($id);
        $cron->setDisabled(!$checked);
        $this->entityManager->flush();
        return $this->json(['ok' => true]);
    }

    private function resume(int $id)
    {   
        $cron = $this->entityManager->getRepository(Cron::class)->findOneById($id);

        $m = $cron->getM();
        $h = $cron->getH();
        $dom = $cron->getDom();
        $mon = $cron->getMon();
        $dow = $cron->getDow();

        $desc = [];

        $mois = [
            1=>"janvier",2=>"février",3=>"mars",4=>"avril",5=>"mai",6=>"juin",
            7=>"juillet",8=>"août",9=>"septembre",10=>"octobre",11=>"novembre",12=>"décembre"
        ];
        $jours = [
            0 => "dimanche", 1 => "lundi", 2 => "mardi", 3 => "mercredi",
            4 => "jeudi", 5 => "vendredi", 6 => "samedi", 7 => "dimanche"
        ];

        if ($dom === "*" && $dow === "*") {
            $desc[] = "Tous les jours";
        } elseif (preg_match('/^(\d+)-(\d+)$/', $dow, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            if (isset($jours[$start]) && isset($jours[$end])) {
                $desc[] = "Chaque semaine du {$jours[$start]} au {$jours[$end]}";
            } else {
                $desc[] = "Chaque semaine (jours $start à $end)";
            }
        } elseif ($dow !== "*") {
            $desc[] = "Chaque semaine le {$jours[(int)$dow]}";
        } elseif ($dom !== "*" && $mon !== "*") {
            $desc[] = "Le $dom {$mois[(int)$mon]}";
        } elseif ($dom !== "*") {
            $desc[] = "Le $dom de chaque mois";
        }

        //It will be complicated to cover all cases, so I ignore the rest.Below is the ignored code.
        // if ($dom === "*" && $dow === "*") {
        //     $desc[] = "Tous les jours";

        // } elseif ($dow !== "*" && $dom === "*") {
        //     if (preg_match('/^(\d+)-(\d+)$/', $dow, $matches)) {
        //         $start = (int)$matches[1];
        //         $end = (int)$matches[2];
        //         $desc[] = "Chaque semaine du {$jours[$start]} au {$jours[$end]}";
        //     } else {
        //         $desc[] = "Chaque semaine le {$jours[(int)$dow]}";
        //     }

        // } elseif ($dom !== "*" && $mon !== "*") {
        //     if (preg_match('/^(\d+)-(\d+)$/', $dom, $matches_dom)) {
        //         $start_dom = (int)$matches_dom[1];
        //         $end_dom = (int)$matches_dom[2];

        //         if (preg_match('/^(\d+)-(\d+)$/', $mon, $matches_mon)) {
        //             $start_mon = (int)$matches_mon[1];
        //             $end_mon = (int)$matches_mon[2];
        //             $desc[] = "Du $start_dom {$mois[$start_mon]} au $end_dom {$mois[$end_mon]}";
        //         } else {
        //             $desc[] = "Du $start_dom au $end_dom {$mois[(int)$mon]}";
        //         }
        
        //     } 
        //     elseif (preg_match('/^(\d+)-(\d+)$/', $mon, $matches_mon)) {
        //         $start_mon = (int)$matches_mon[1];
        //         $end_mon = (int)$matches_mon[2];
        //         $desc[] = "Le $dom de {$mois[$start_mon]} à {$mois[$end_mon]}";
        //     } 
        //     else {
        //         $desc[] = "Le $dom {$mois[(int)$mon]}";
        //     }

        // } elseif ($dom !== "*") {
        //     if (preg_match('/^(\d+)-(\d+)$/', $dom, $matches)) {
        //         $desc[] = "Du {$matches[1]} au {$matches[2]} de chaque mois";
        //     } else {
        //         $desc[] = "Le $dom de chaque mois";
        //     }
        // }


        if (preg_match('/^\*\/(\d+)$/', $m, $matches)) {
            $step = $matches[1];
            if ($h === "*") {
                $desc[] = "toutes les $step minutes";
            } elseif (preg_match('/^(\d+)-(\d+)$/', $h, $hm)) {
                $desc[] = "toutes les $step minutes entre {$hm[1]}h et {$hm[2]}h";
            } else {
                $desc[] = "toutes les $step minutes à {$h}h";
            }
        }
        elseif ($m === "*" and $h === "*") {
            $desc[] = "chaque minute chaque heure";
        }
        elseif (is_numeric($m) && $h === "*") {
            $desc[] = "à la minute $m de chaque heure";
        }
        elseif ($m === "*" && is_numeric($h)) {
            $desc[] = "chaque minute à {$h}h";
        }
        else {
            $h_int = is_numeric($h) ? (int)$h : 0;
            $m_int = is_numeric($m) ? (int)$m : 0;
            $time = sprintf("%02d:%02d", $h_int, $m_int);
            $desc[] = "à $time";
        }

        return implode(" ", $desc);
    }
}
