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
    public function index(Request $request, Session $session): Response
    {
        // Temporary folder
        $tmp_dir = sys_get_temp_dir();

        $crons = $this->entityManager->getRepository(Cron::class)
            ->findAll();

        $elements = array();
        foreach ($crons as $cron) {
            $elem = array(
                'cron' => $cron,
                'resume' => $this->resume($cron->getId())
            );

            $elements[] = $elem;
        }


        $this->templateParams(array(
            'elements'  => $elements,
        ));

        return $this->output('crontab/index.html.twig');
    }

    #[Route(path: '/crontab', name: 'crontab.update', methods: ['POST'])]
    public function update(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $params = $request->request->all();
        
        if (
            !isset($params['min']) ||
            !isset($params['hour']) ||
            !isset($params['dom']) ||
            !isset($params['mon']) ||
            !isset($params['dow'])
        ) {
            $error = 'La modification de l\'ordonnanceur est vide.';
        }

        else {
            $cron = $this->entityManager->getRepository(Cron::class)->find($params['id']);

            if($this->verif($cron->getM(), $params['hour'], $params['dom'], $params['mon'], $params['dow']) === false) {
                $error = 'Les valeurs saisies ne sont pas correctes.';
            } else {
                try {
                    $cron->setM($params['min']);
                    $cron->setH($params['hour']);
                    $cron->setDom($params['dom']);
                    $cron->setMon($params['mon']);
                    $cron->setDow($params['dow']);
                    $cron->setDisabled($params['disabled']);
                }
                catch (Exception $e) {
                    $error = 'Une erreur est survenue pendant la modification de la crontab !';
                }

                $this->entityManager->flush();
            }
        }

        if (isset($error)) {
            $session->getFlashBag()->add('error', $error);
        } else {
            $flash = 'La crontab a été modifiée avec succès';
            $session->getFlashBag()->add('notice', $flash);
        }

        return $this->redirectToRoute('crontab.index');
    }

    #[Route(path: '/crontab/{id}', name: 'crontab.edit', methods: ['GET'])]
    public function edit(Request $request)
    {
        $id = $request->get('id');
        $cron = $this->entityManager->getRepository(Cron::class)->find($id);

        $this->templateParams(array(
            'cron'  => $cron,
        ));

        return $this->output('crontab/edit.html.twig');
    }

    #[Route(path: '/crontab/disabled', name: 'crontab.disabled', methods: ['POST'])]
    public function disabled(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');
        $checked = $request->get('checked');

        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        $cron = $this->entityManager->getRepository(Cron::class)->find($id);
        $cron->setDisabled(!$checked);
        $this->entityManager->flush();

        return $this->json(['ok' => true]);
    }

    private function verif(string $minutes, string $hour, string $dayOfMonth, string $month, string $dayOfWeek): bool
    {

        $rules = [
            'minutes' => [0, 59],
            'hour' => [0, 23],
            'dayOfMonth' => [1, 31],
            'month' => [1, 12],
            'dayOfWeek' => [0, 7],
        ];

        foreach (['minutes', 'hour', 'dayOfMonth', 'month', 'dayOfWeek'] as $field) {
            $value = $$field;
            [$min, $max] = $rules[$field];

            if ($value === '*') {
                continue;
            }

            if (preg_match('/^\*\/(\d+)$/', $value, $m)) {
                $n = (int)$m[1];
                if ($n < 1 || $n > $max) {
                    return false;
                }
                continue;
            }

            if (preg_match('/^(\d+)-(\d+)$/', $value, $m)) {
                [$start, $end] = [(int)$m[1], (int)$m[2]];
                if ($start < $min || $end > $max || $start > $end) {
                    return false;
                }
                continue;
            }

            if (preg_match('/^(\d+)$/', $value, $m)) {
                $num = (int)$m[1];
                if ($num < $min || $num > $max) {
                    return false;
                }
                continue;
            }

            return false;

        }

        return true;

    }

    private function resume(int $id)
    {   
        $cron = $this->entityManager->getRepository(Cron::class)->find($id);

        $m = $cron->getM();
        $h = $cron->getH();
        $dom = $cron->getDom();
        $mon = $cron->getMon();
        $dow = $cron->getDow();

        $desc = [];

        $mois = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin',
            7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];
        $jours = [
            0 => 'dimanche', 1 => 'lundi', 2 => 'mardi', 3 => 'mercredi',
            4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'
        ];

        if ($dom === '*' && $dow === '*') {
            $desc[] = 'Tous les jours';
        } 

        if ($dom !== '*') {
            if (preg_match('/^(\d+)-(\d+)$/', $dom, $matches)) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                $desc[] = "Du $start au $end";
            } else {
                $desc[] = "Chaque $dom";
            }
        }
        
        if ($dow !== '*') {
            if (preg_match('/^(\d+)-(\d+)$/', $dow, $matches)) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                $desc[] = "Chaque semaine du {$jours[$start]} au {$jours[$end]}";
            } else {
                $desc[] = "Chaque semaine le {$jours[(int)$dow]}";
            }
        } 
        
        if ($mon !== '*') {
             if (preg_match('/^(\d+)-(\d+)$/', $mon, $matches)) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                $desc[] = "de {$mois[$start]} à {$mois[$end]}";
            } else {
                $desc[] = "chaque {$mois[(int)$mon]}";
            }
        }

        if (preg_match('/^\*\/(\d+)$/', $m, $matches)) {
            $step = $matches[1];
            if ($h === '*') {
                $desc[] = "toutes les $step minutes";
            } elseif (preg_match('/^(\d+)-(\d+)$/', $h, $hm)) {
                $desc[] = "toutes les $step minutes entre {$hm[1]}h et {$hm[2]}h";
            } else {
                $desc[] = "toutes les $step minutes à {$h}h";
            }
        } elseif ($m === '*' and $h === '*') {
            $desc[] = 'chaque minute chaque heure';
        } elseif (is_numeric($m) && $h === '*') {
            $desc[] = "à la minute $m de chaque heure";
        } elseif ($m === '*' && is_numeric($h)) {
            $desc[] = "chaque minute à {$h}h";
        } elseif (is_numeric($m) && is_numeric($h)) {
            $desc[] = sprintf('à %02dh%02d', $h, $m);
        }

        return implode(' ', $desc);
    }
}
