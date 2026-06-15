<?php

namespace App\Service;

use CJMail;
use Exception;
use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\Holiday;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    public function __construct(
        protected \Twig\Environment $twig,
        protected TranslatorInterface $translator,
    )
    {
    }

    /**
     * @param string[] $to
     */
    protected function sendWithCJMail(string $subject, string $htmlBody, array $to): void
    {
        $cjmail = new CJMail;
        $cjmail->subject = $subject;
        $cjmail->message = $htmlBody;
        $cjmail->to = $to;
        $cjmail->nl2br = false;
        $cjmail->send();

        if ($cjmail->error) {
            throw new Exception($cjmail->error_CJInfo);
        }
    }

    public function sendDeletedHolidayNotification(Holiday $holiday): void
    {
        global $entityManager;
        $configRepository = $entityManager->getRepository(Config::class);

        $agent = $entityManager->find(Agent::class, $holiday->getUser());

        if ($configRepository->getValue('Conges-Recuperations') and $holiday->getDebit() == 'recuperation') {
            $title = 'Compensatory time deletion';
        } else {
            $title = 'Holiday deletion';
        }

        $htmlBody = $this->twig->render('mail/deleted-holiday-notification.html.twig', [
            'title' => $title,
            'holiday' => $holiday,
            'agent' => $agent,
        ]);

        $start = $holiday->getStart()->format('Y-m-d H:i:s');
        $end = $holiday->getEnd()->format('Y-m-d H:i:s');

        if ($configRepository->getValue('Absences-notifications-agent-par-agent')) {
            $a = new \absences();
            $a->getRecipients2(null, $agent->getId(), 2, 500, $start, $end);
            $recipients = $a->recipients;
        } else {
            $c = new \conges();
            $c->getResponsables($start, $end, $agent->getId());
            $a = new \absences();
            $a->getRecipients("-A2", $c->responsables, $agent);
            $recipients = $a->recipients;
        }

        $subject = $this->translator->trans($title);

        $this->sendWithCJMail($subject, $htmlBody, $recipients);
    }
}
