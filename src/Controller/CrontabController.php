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
                'minute'         => $cron->getM(),
                'hour'           => $cron->getH(),
                'day_of_month'   => $cron->getDom(),
                'month'          => $cron->getMon(),
                'day_of_week'    => $cron->getDow(),
                'command_name'   => $cron->getCommand(),
                'comment'        => $cron->getComment(),
                'disabled'       => $cron->isDisabled(),
                'last'           => $cron->getLast()
            );
        }

        $this->templateParams(array(
            'elements'  => $elements,
            'error'     => $request->query->get('error'),
            'post'      => $request->query->get('post'),
            'warning'   => $request->query->get('warning')
        ));

        return $this->output('crontab/index.html.twig');
    }

        #[Route(path: '/crontab', name: 'crontab.update')] // , methods={"POST"})
    public function update(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $params = $request->request->all();
        // Demo mode
        if ($params && !empty($this->config('demo'))) {
            $error = "La modification de la configuration n'est pas autorisée sur la version de démonstration.";
            $error .= "#BR#Merci de votre compréhension";
        }
        elseif ($params) {

            $technical = $request->get('technical');

            $configParams = $this->entityManager->getRepository(Config::class)->findBy(
                array('technical' => $technical),
                array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
            );

            foreach ($configParams as $cp) {
                if (in_array($cp->getType(), ['hidden', 'info'])) {
                    continue;
                }
                // boolean and checkboxes elements.
                if (!isset($params[$cp->getName()])) {
                    if ($cp->getType() == 'boolean') {
                        $params[$cp->getName()] = '0';
                    } else {
                        $params[$cp->getName()] = array();
                    }
                }
                $value = $params[$cp->getName()];

                if (is_string($value)) {
                    $value = trim($value);
                }

                // Passwords
                if (substr($cp->getName(), -9) == '-Password') {
                    $value = encrypt($value);
                }
                // Checkboxes
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if ($cp->getType() == 'color') {
                    $value = filter_var($value, FILTER_CALLBACK, ['options' => 'sanitize_color']);
                }

                try {
                    $cp->setValue($value);
                    $this->entityManager->persist($cp);
                }
                catch (Exception $e) {
                    $error = 'Une erreur est survenue pendant la modification de la configuration !';
                }
            }
            $this->entityManager->flush();

        }

        if (isset($error)) {
            $session->getFlashBag()->add('error', $error);
        } else {
            $flash = 'La configuration a été modifiée avec succès';
            $session->getFlashBag()->add('notice', $flash);
        }

        $options = $technical ? ['options' => 'technical'] : [];

        return $this->redirectToRoute('config.index', $options);
    }
}
