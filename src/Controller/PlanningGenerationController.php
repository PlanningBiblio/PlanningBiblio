<?php

namespace App\Controller;

use App\Controller\BaseController;

use App\Entity\Agent;
use App\Entity\PlanningGenerationJob;
use App\Entity\PlanningPositionLines;
use App\Entity\PlanningPositionTab;
use App\Entity\PlanningPositionTabAffectation;
use App\Entity\Position;
use App\Message\GeneratePlanningMessage;
use App\Planno\PlanningGenerationPayloadBuilder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class PlanningGenerationController extends BaseController
{
    #[Route('/admin/planning-generation', name: 'planning_generation.index', methods: ['GET'])]
    public function index(Request $request)
    {
        $jobs = $this->entityManager->getRepository(PlanningGenerationJob::class)
            ->findBy([], ['date_generation' => 'DESC']);

        $sites = [];
        $nbSites = $this->config('Multisites-nombre');
        if ($nbSites > 1) {
            for ($i = 1; $i <= $nbSites; $i++) {
                if (!empty($this->config('Multisites-site' . $i))) {
                    $sites[] = ['id' => $i, 'nom' => $this->config('Multisites-site' . $i)];
                }
            }
        } else {
            $sites[] = ['id' => 1, 'nom' => null];
        }

        $hasOngoingJob = $this->entityManager->getRepository(PlanningGenerationJob::class)
            ->findOneBy(['statut' => PlanningGenerationJob::STATUT_EN_COURS]) !== null;

        $this->templateParams([
            'jobs' => $jobs,
            'sites' => $sites,
            'has_ongoing_job' => $hasOngoingJob,
            'manual_json_enabled' => !empty($this->config('PlanningGeneration-ManualJson')),
            'CSRFSession' => $GLOBALS['CSRFSession'],
        ]);

        return $this->output('planning-generation/index.html.twig');
    }

    #[Route('/admin/planning-generation/{id<\d+>}/json/{type<sent|received>}', name: 'planning_generation.json', methods: ['GET'])]
    public function viewJson(Request $request, int $id, string $type): Response
    {
        $job = $this->entityManager->getRepository(PlanningGenerationJob::class)->find($id);

        if (!$job) {
            return new Response('Not found', 404);
        }

        $data = $type === 'sent' ? $job->getJsonEnvoye() : $job->getJsonRecu();

        return new Response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    #[Route('/admin/planning-generation/{id<\d+>}/statistics', name: 'planning_generation.statistics', methods: ['GET'])]
    public function statistics(int $id): Response
    {
        $job = $this->entityManager->getRepository(PlanningGenerationJob::class)->find($id);

        if (!$job) {
            return new Response('Not found', 404);
        }

        $jsonRecu = $job->getJsonRecu();
        $statistiques = $jsonRecu['optimise']['statistiques'] ?? null;

        if ($statistiques) {
            $statistiques = $this->resolveAgentNames($statistiques);
        }

        $this->templateParams([
            'job' => $job,
            'statistiques' => $statistiques,
            'conflict_message' => $this->buildConflictMessage($job->getConflicts()),
            'error_message' => $job->getErrorMessage(),
        ]);

        return $this->output('planning-generation/statistics.html.twig');
    }

    /**
     * Construit le message signalant les agents non importés faute de disponibilité (voir
     * GeneratePlanningMessageHandler::hasUnavailability), avec accord singulier/pluriel.
     */
    private function buildConflictMessage(?array $conflicts): ?string
    {
        if (!$conflicts) {
            return null;
        }

        $agentNames = array_values(array_unique(array_column($conflicts, 'agent')));
        $posteDescriptions = array_values(array_unique(array_map(
            fn ($c) => "{$c['poste']} ({$c['date']} {$c['debut']}-{$c['fin']})",
            $conflicts
        )));

        $multipleAgents = count($agentNames) > 1;
        $multiplePostes = count($posteDescriptions) > 1;

        $agentLabel = ($multipleAgents ? 'Les agents ' : "L'agent ") . implode(', ', $agentNames);
        $verbe = $multipleAgents ? "n'ont" : "n'a";
        $participe = $multipleAgents ? 'placés' : 'placé';
        $posteLabel = ($multiplePostes ? 'les postes ' : 'le poste ') . implode(', ', $posteDescriptions);

        return "$agentLabel $verbe pas pu être $participe sur $posteLabel car une indisponibilité a été déposée pendant la génération du planning.";
    }

    /**
     * Les agents sont envoyés à l'algorithme sous forme d'id (et non de login) pour les anonymiser ;
     * cette méthode reconvertit ces ids en noms lisibles pour l'affichage des statistiques.
     */
    private function resolveAgentNames(array $statistiques): array
    {
        $agentName = function (string $id) {
            if (!ctype_digit($id)) {
                return $id;
            }

            $agent = $this->entityManager->getRepository(Agent::class)->find((int) $id);

            if (!$agent) {
                return $id;
            }

            $name = trim($agent->getFirstname() . ' ' . $agent->getLastname());

            return $name ?: $agent->getLogin();
        };

        foreach ($statistiques['agents'] ?? [] as $key => $agentStats) {
            if (isset($agentStats['nom_agent'])) {
                $statistiques['agents'][$key]['agent_id'] = $agentStats['nom_agent'];
                $statistiques['agents'][$key]['nom_agent'] = $agentName((string) $agentStats['nom_agent']);
            }
        }

        foreach ($statistiques['equilibre']['agents_exclus'] ?? [] as $key => $excludedId) {
            $statistiques['equilibre']['agents_exclus'][$key] = $agentName((string) $excludedId);
        }

        return $statistiques;
    }

    #[Route('/admin/planning-generation/active-tables', name: 'planning_generation.active_tables', methods: ['GET'])]
    public function activeTables(Request $request): Response
    {
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $site = $request->get('site');

        if (!$dateDebut || !$dateFin) {
            return new Response(json_encode(['error' => 'date_debut et date_fin sont requis']), 400, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from(PlanningPositionTabAffectation::class, 'a')
            ->andWhere('a.date >= :debut')
            ->andWhere('a.date <= :fin')
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin);

        if ($site) {
            $qb->andWhere('a.site = :site')->setParameter('site', $site);
        }

        $affectations = $qb->getQuery()->getResult();

        $cadres = [];
        foreach ($affectations as $affectation) {
            $cadres[$affectation->getTable()] = true;
        }

        $result = [];
        foreach (array_keys($cadres) as $numero) {
            $tab = $this->entityManager->getRepository(PlanningPositionTab::class)->findOneBy(['tableau' => $numero]);

            $lignes = $this->entityManager->getRepository(PlanningPositionLines::class)->findBy([
                'numero' => $numero,
                'type' => 'poste',
            ]);

            $postes = [];
            foreach ($lignes as $ligne) {
                if (!$ligne->getPosition()) {
                    continue;
                }
                $position = $this->entityManager->getRepository(Position::class)->find($ligne->getPosition());
                if ($position) {
                    $postes[$position->getId()] = $position->getName();
                }
            }

            $result[] = [
                'numero' => $numero,
                'nom' => $tab ? $tab->getName() : "Tableau $numero",
                'postes' => $postes,
            ];
        }

        return new Response(json_encode($result), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    #[Route('/admin/planning-generation/generate', name: 'planning_generation.generate', methods: ['POST'])]
    public function generate(Request $request, MessageBusInterface $messageBus): Response
    {
        if (!$this->csrf_protection($request)) {
            return new Response(json_encode(['error' => 'The CSRF token is invalid. Please try to resubmit the form.']));
        }

        $ongoingJob = $this->entityManager->getRepository(PlanningGenerationJob::class)
            ->findOneBy(['statut' => PlanningGenerationJob::STATUT_EN_COURS]);

        if ($ongoingJob) {
            return new Response(json_encode(['error' => 'Une génération de planning est déjà en cours. Merci d\'attendre qu\'elle se termine avant d\'en lancer une nouvelle.']), 409, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $dateDebut = \DateTime::createFromFormat('Y-m-d', $request->get('date_debut'));
        $dateFin = \DateTime::createFromFormat('Y-m-d', $request->get('date_fin'));
        $site = $request->get('site') ? (int) $request->get('site') : null;
        $manualJson = $request->get('manual_json');

        if (!$dateDebut || !$dateFin) {
            return new Response(json_encode(['error' => 'Dates invalides']), 400, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        // createFromFormat('Y-m-d', ...) conserve l'heure courante du serveur au lieu de minuit,
        // ce qui fausse les comparaisons avec les dates de fin (minuit) stockées en base.
        $dateDebut->setTime(0, 0, 0);
        $dateFin->setTime(0, 0, 0);

        if ($manualJson !== null && $manualJson !== '') {
            if (empty($this->config('PlanningGeneration-ManualJson'))) {
                return new Response(json_encode(['error' => 'La génération via un JSON personnalisé est désactivée (Configuration technique > Génération de planning).']), 403, ['Content-Type' => 'application/json; charset=utf-8']);
            }

            $payload = json_decode($manualJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
                return new Response(json_encode(['error' => 'Le JSON fourni est invalide.']), 400, ['Content-Type' => 'application/json; charset=utf-8']);
            }
        } else {
            $excluded = json_decode($request->get('excluded_postes', '{}'), true) ?: [];
            $payloadBuilder = new PlanningGenerationPayloadBuilder($this->entityManager);
            $payload = $payloadBuilder->build($dateDebut, $dateFin, $site, $excluded);
        }

        $job = new PlanningGenerationJob();
        $job->setDateGeneration(new \DateTime());
        $job->setDateDebut($dateDebut);
        $job->setDateFin($dateFin);
        $job->setSite($site);
        $job->setJsonEnvoye($payload);
        $job->setStatut(PlanningGenerationJob::STATUT_EN_COURS);
        $job->setCreatedBy($_SESSION['login_id'] ?? null);

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        $messageBus->dispatch(new GeneratePlanningMessage($job->getId()));

        return new Response(json_encode(['id' => $job->getId()]), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    #[Route('/admin/planning-generation/{id<\d+>}', name: 'planning_generation.delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        if (!$this->csrf_protection($request)) {
            return new Response(json_encode(['error' => 'The CSRF token is invalid. Please try to resubmit the form.']));
        }

        $job = $this->entityManager->getRepository(PlanningGenerationJob::class)->find($id);

        if (!$job) {
            return new Response(json_encode(['error' => 'Not found']), 404, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        if ($job->getStatut() === PlanningGenerationJob::STATUT_EN_COURS) {
            return new Response(json_encode(['error' => 'Impossible de supprimer une génération en cours.']), 409, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $this->entityManager->remove($job);
        $this->entityManager->flush();

        return new Response(json_encode(['ok' => true]), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    #[Route('/admin/planning-generation/status', name: 'planning_generation.status', methods: ['GET'])]
    public function status(Request $request): Response
    {
        $jobs = $this->entityManager->getRepository(PlanningGenerationJob::class)->findAll();

        $result = [];
        foreach ($jobs as $job) {
            $result[$job->getId()] = $job->getStatut();
        }

        return new Response(json_encode($result), 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }
}
