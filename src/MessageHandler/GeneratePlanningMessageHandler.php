<?php

namespace App\MessageHandler;

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\PlanningGenerationJob;
use App\Entity\PlanningPosition;
use App\Entity\Position;
use App\Message\GeneratePlanningMessage;
use App\Planno\PlanningGenerationClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GeneratePlanningMessageHandler
{
    use \App\Traits\LoggerTrait;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(GeneratePlanningMessage $message): void
    {
        $job = $this->entityManager->getRepository(PlanningGenerationJob::class)->find($message->getJobId());

        if (!$job) {
            return;
        }

        $config = $this->entityManager->getRepository(\App\Entity\Config::class)->getAll();
        $client = new PlanningGenerationClient($config['PlanningGeneration-ApiUrl'] ?? null, $config['PlanningGeneration-ApiKey'] ?? null);

        try {
            $response = $client->send($job->getJsonEnvoye());
        } catch (\Exception $e) {
            $job->setStatut(PlanningGenerationJob::STATUT_ECHEC);
            $job->setErrorMessage($e->getMessage());
            $this->entityManager->flush();

            return;
        }

        $job->setJsonRecu($response);

        if (($response['statut'] ?? null) !== 'succès' && ($response['statut'] ?? null) !== 'succes') {
            $job->setStatut(PlanningGenerationJob::STATUT_ECHEC);
            $job->setErrorMessage('L\'API a répondu avec un statut différent de succès.');
            $this->entityManager->flush();

            return;
        }

        $conflicts = $this->importPlanning($response, $job->getSite());

        $job->setStatut($conflicts ? PlanningGenerationJob::STATUT_CONFLIT : PlanningGenerationJob::STATUT_SUCCES);
        $job->setConflicts($conflicts ?: null);
        $this->entityManager->flush();
    }

    /**
     * Importe les affectations reçues, à l'exception de celles pour lesquelles l'agent a déposé une
     * indisponibilité (absence/congé/récupération) chevauchant le créneau depuis l'envoi du JSON à
     * l'algorithme (l'algorithme a travaillé sur une version désormais périmée des disponibilités).
     * Ces affectations en conflit ne sont pas importées ; elles sont retournées pour être signalées.
     *
     * @return array<int, array{agent: string, poste: string, date: string, debut: string, fin: string}>
     */
    private function importPlanning(array $response, ?int $site): array
    {
        $creneaux = $response['optimise']['planning']['creneaux'] ?? [];
        $conflicts = [];

        foreach ($creneaux as $creneau) {
            $date = \DateTime::createFromFormat('d/m/Y', $creneau['date']);
            $debut = \DateTime::createFromFormat('H:i', $creneau['debut']);
            $fin = \DateTime::createFromFormat('H:i', $creneau['fin']);

            if (!$date || !$debut || !$fin) {
                continue;
            }

            foreach ($creneau['postes'] ?? [] as $posteName => $posteData) {
                foreach ($posteData['agents_postes'] ?? [] as $assignment) {
                    // Les affectations déjà existantes (bloque=true) ont été envoyées telles quelles, pas besoin de les réécrire.
                    if (!empty($assignment['bloque'])) {
                        continue;
                    }

                    $position = $this->entityManager->getRepository(Position::class)->findOneBy(['nom' => $posteName]);
                    $agent = ctype_digit((string) $assignment['nom']) ? $this->entityManager->getRepository(Agent::class)->find((int) $assignment['nom']) : null;

                    if (!$position || !$agent) {
                        $this->log("Import planning généré : poste ou agent introuvable ($posteName / {$assignment['nom']})", 'PlanningGeneration');
                        continue;
                    }

                    if ($this->hasUnavailability($agent, $date, $debut, $fin)) {
                        $conflicts[] = [
                            'agent' => trim($agent->getFirstname() . ' ' . $agent->getLastname()) ?: $agent->getLogin(),
                            'poste' => $posteName,
                            'date' => $creneau['date'],
                            'debut' => $creneau['debut'],
                            'fin' => $creneau['fin'],
                        ];
                        continue;
                    }

                    $planningPosition = new PlanningPosition();
                    $planningPosition->setDate(clone $date);
                    $planningPosition->setPosition($position->getId());
                    $planningPosition->setStart(clone $debut);
                    $planningPosition->setEnd(clone $fin);
                    $planningPosition->setUser($agent->getId());
                    $planningPosition->setAbsent(0);
                    $planningPosition->setDelete(false);
                    $planningPosition->setGrey(false);
                    $planningPosition->setSite($site ?? 1);

                    $this->entityManager->persist($planningPosition);
                }
            }
        }

        return $conflicts;
    }

    /**
     * Vérifie si l'agent a déposé une absence ou un congé/récupération validé (ou en attente de validation,
     * cf. PlanningGenerationPayloadBuilder::buildIndisponibilites) qui chevauche le créneau [date debut-fin].
     */
    private function hasUnavailability(Agent $agent, \DateTime $date, \DateTime $debut, \DateTime $fin): bool
    {
        $creneauDebut = (clone $date)->setTime((int) $debut->format('H'), (int) $debut->format('i'));
        $creneauFin = (clone $date)->setTime((int) $fin->format('H'), (int) $fin->format('i'));

        $absence = $this->entityManager->createQueryBuilder()
            ->select('a')->from(Absence::class, 'a')
            ->andWhere('a.perso_id = :agent')
            ->andWhere('a.debut < :fin')
            ->andWhere('a.fin > :debut')
            ->andWhere('a.valide > 0')
            ->setParameter('agent', $agent->getId())
            ->setParameter('debut', $creneauDebut)
            ->setParameter('fin', $creneauFin)
            ->getQuery()
            ->getOneOrNullResult();

        if ($absence) {
            return true;
        }

        if (empty($GLOBALS['config']['Conges-Enable'])) {
            return false;
        }

        $conge = $this->entityManager->createQueryBuilder()
            ->select('h')->from(Holiday::class, 'h')
            ->andWhere('h.perso_id = :agent')
            ->andWhere('h.debut < :fin')
            ->andWhere('h.fin > :debut')
            ->andWhere('h.valide >= 0')
            ->andWhere('h.valide_n1 >= 0')
            ->andWhere('h.information = 0')
            ->andWhere('h.supprime = 0')
            ->setParameter('agent', $agent->getId())
            ->setParameter('debut', $creneauDebut)
            ->setParameter('fin', $creneauFin)
            ->getQuery()
            ->getOneOrNullResult();

        return $conge !== null;
    }
}
