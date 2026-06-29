<?php

namespace App\Service;

use CJMail;
use Exception;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Holiday;
use App\Entity\Agent;
use App\Entity\Config;

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
        $cjmail->send();

        if ($cjmail->error) {
            throw new Exception($cjmail->error_CJInfo);
        }
    }

    public function sendDeletedHolidayNotification(Holiday $holiday): void
    {
        global $entityManager;
        global $config;

        $agent = $entityManager->find(Agent::class, $holiday->getUser());

        $htmlBody = $this->twig->render('mail/deleted-holiday-notification.html.twig', ['holiday' => $holiday, 'agent' => $agent]);

        $start_sql = $holiday->getStart()->format('Y-m-d H:i:s');
        $end_sql = $holiday->getEnd()->format('Y-m-d H:i:s');

        $configRepository = $entityManager->getRepository(Config::class);

        if ($configRepository->getValue('Absences-notifications-agent-par-agent')) {
            $a = new \absences();
            $a->getRecipients2(null, $agent->getId(), 2, 500, $start_sql, $end_sql);
            $recipients = $a->recipients;
        } else {
            $c = new \conges();
            $c->getResponsables($start_sql, $end_sql, $agent->getId());
            $a = new \absences();
            $a->getRecipients("-A2", $c->responsables, $agent);
            $recipients = $a->recipients;
        }

        $subject = $this->translator->trans("Holiday deletion");

        $this->sendWithCJMail($subject, $htmlBody, $recipients);
    }
}
