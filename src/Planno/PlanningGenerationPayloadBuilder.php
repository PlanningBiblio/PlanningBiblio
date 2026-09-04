<?php

namespace App\Planno;

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionExpectedStaffDate;
use App\Entity\PlanningPositionTab;
use App\Entity\PlanningPositionTabAffectation;
use App\Entity\Position;
use App\Entity\WorkingHour;
use App\Planno\Helper\HourHelper;
use Doctrine\ORM\EntityManagerInterface;

require_once(__DIR__ . '/../../legacy/Common/function.php');

class PlanningGenerationPayloadBuilder
{
    private const JOURS_FR = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    // Motifs d'absence considérés comme des congés (le reste est classé "absence").
    private const MOTIFS_CONGE = ['congés payés', 'maladie', 'congé paternité', 'congé maternité'];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param array<int, int[]> $excludedPostesByNumero Map cadre (numero) => liste des ids de postes exclus
     */
    public function build(\DateTime $dateDebut, \DateTime $dateFin, ?int $site, array $excludedPostesByNumero = []): array
    {
        $creneaux = $this->buildCreneaux($dateDebut, $dateFin, $site, $excludedPostesByNumero);
        $agents = $this->buildAgents($dateDebut, $dateFin, $site);

        return [
            'creneaux' => $creneaux,
            'agents' => $agents,
        ];
    }

    private function buildCreneaux(\DateTime $dateDebut, \DateTime $dateFin, ?int $site, array $excludedPostesByNumero): array
    {
        $creneaux = [];
        $positionNames = [];

        $current = clone $dateDebut;
        while ($current <= $dateFin) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('a')->from(PlanningPositionTabAffectation::class, 'a')
                ->andWhere('a.date = :date')
                ->setParameter('date', $current->format('Y-m-d'));
            if ($site) {
                $qb->andWhere('a.site = :site')->setParameter('site', $site);
            }
            $affectation = $qb->getQuery()->getOneOrNullResult();

            if ($affectation) {
                $numero = $affectation->getTable();
                $excluded = $excludedPostesByNumero[$numero] ?? [];
                $overrides = $this->getExpectedStaffOverrides($numero, $current);

                $framework = new Framework();
                $framework->id = $numero;
                $framework->get();

                $planningTab = $this->entityManager->getRepository(PlanningPositionTab::class)->findOneBy(['tableau' => $numero]);

                foreach ($framework->elements as $sousTableauId => $sousTableau) {
                    $nomTableau = $sousTableau['titre'] ?: ($planningTab ? $planningTab->getName() : "Tableau $sousTableauId");

                    foreach ($sousTableau['horaires'] as $idx => $horaire) {
                        $colonne = $idx + 1;
                        $postesData = [];

                        foreach ($sousTableau['lignes'] as $ligne) {
                            if ($ligne['type'] !== 'poste' || !$ligne['poste'] || in_array($ligne['poste'], $excluded)) {
                                continue;
                            }

                            if (!isset($positionNames[$ligne['poste']])) {
                                $position = $this->entityManager->getRepository(Position::class)->find($ligne['poste']);
                                $positionNames[$ligne['poste']] = $position ? $position->getName() : null;
                            }
                            $posteName = $positionNames[$ligne['poste']];
                            if (!$posteName) {
                                continue;
                            }

                            // Une cellule grisée signifie que ce poste n'est pas à pourvoir sur ce créneau,
                            // quel que soit l'effectif attendu configuré par ailleurs. Deux mécanismes de
                            // grisage existent : statique au gabarit (cellules_grises) et ponctuel pour cette
                            // date précise (pl_poste.grise, ex: import de modèle, "bataille navale").
                            if (in_array("{$ligne['ligne']}_{$colonne}", $sousTableau['cellules_grises'])
                                || $this->isCellGreyedForDate($current, (int) $ligne['poste'], $horaire['debut'], $horaire['fin'], $site)) {
                                $attendu = 0;
                            } else {
                                $overrideKey = "{$sousTableauId}_{$ligne['ligne']}_{$colonne}";
                                $attendu = $overrides[$overrideKey] ?? ($sousTableau['effectifs_attendus']["{$ligne['ligne']}_{$colonne}"] ?? 1);
                            }

                            $postesData[$posteName] = [
                                'attendu' => (int) $attendu,
                                'agents_postes' => $this->getExistingAssignments($current, (int) $ligne['poste'], $horaire['debut'], $horaire['fin'], $site),
                            ];
                        }

                        if (empty($postesData)) {
                            continue;
                        }

                        $creneaux[] = [
                            'nom_tableau' => $nomTableau,
                            'date' => $current->format('d/m/Y'),
                            'jour' => self::JOURS_FR[(int) $current->format('N') - 1],
                            'id_creneau' => (string) $colonne,
                            'debut' => substr($horaire['debut'], 0, 5),
                            'fin' => substr($horaire['fin'], 0, 5),
                            'postes' => $postesData,
                        ];
                    }
                }
            }

            $current->modify('+1 day');
        }

        return $creneaux;
    }

    /**
     * Surcharges ponctuelles (par date) de l'effectif attendu pour le cadre $numero, indexées par
     * "sousTableau_ligne_colonne". Une entrée présente ici est prioritaire sur le gabarit récurrent
     * du tableau (voir pl_poste_effectif_attendu_date).
     *
     * @return array<string, int>
     */
    private function getExpectedStaffOverrides(int $numero, \DateTime $date): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('o')->from(PlanningPositionExpectedStaffDate::class, 'o')
            ->andWhere('o.numero = :numero')
            ->andWhere('o.date = :date')
            ->setParameter('numero', $numero)
            ->setParameter('date', $date->format('Y-m-d'));

        $overrides = [];
        foreach ($qb->getQuery()->getResult() as $override) {
            $key = "{$override->getTableau()}_{$override->getLigne()}_{$override->getColonne()}";
            $overrides[$key] = $override->getExpectedStaff();
        }

        return $overrides;
    }

    /**
     * Grisage ponctuel d'une cellule poste/créneau pour la date précise (pl_poste.grise), par opposition
     * au grisage statique du gabarit (pl_poste_cellules) déjà géré via $sousTableau['cellules_grises'].
     */
    private function isCellGreyedForDate(\DateTime $date, int $posteId, string $debut, string $fin, ?int $site): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')->from(PlanningPosition::class, 'p')
            ->andWhere('p.date = :date')
            ->andWhere('p.poste = :poste')
            ->andWhere('p.debut = :debut')
            ->andWhere('p.fin = :fin')
            ->andWhere('p.grise = 1')
            ->andWhere('p.supprime = 0 OR p.supprime IS NULL')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('poste', $posteId)
            ->setParameter('debut', \DateTime::createFromFormat('H:i:s', $debut))
            ->setParameter('fin', \DateTime::createFromFormat('H:i:s', $fin));
        if ($site) {
            $qb->andWhere('p.site = :site')->setParameter('site', $site);
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    private function getExistingAssignments(\DateTime $date, int $posteId, string $debut, string $fin, ?int $site): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')->from(PlanningPosition::class, 'p')
            ->andWhere('p.date = :date')
            ->andWhere('p.poste = :poste')
            ->andWhere('p.debut < :fin')
            ->andWhere('p.fin > :debut')
            ->andWhere('p.supprime = 0 OR p.supprime IS NULL')
            ->andWhere('p.perso_id IS NOT NULL')
            ->andWhere('p.perso_id > 0')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('poste', $posteId)
            ->setParameter('debut', \DateTime::createFromFormat('H:i:s', $debut))
            ->setParameter('fin', \DateTime::createFromFormat('H:i:s', $fin));
        if ($site) {
            $qb->andWhere('p.site = :site')->setParameter('site', $site);
        }

        $assignments = [];
        foreach ($qb->getQuery()->getResult() as $planningPosition) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($planningPosition->getUser());
            if ($agent) {
                // L'id (et non le login) est envoyé à l'algorithme pour anonymiser les agents ;
                // GeneratePlanningMessageHandler reconvertit cet id en agent à la réception de la réponse.
                $assignments[] = ['nom' => (string) $agent->getId(), 'bloque' => true];
            }
        }

        return $assignments;
    }

    /**
     * PHP ne distingue pas tableau vide et objet vide : json_encode([]) produit "[]", pas "{}".
     * L'API attend un objet (dictionnaire) pour quotas_postes/indisponibilites même quand ils sont vides.
     */
    private function asJsonObject(array $value): array|\stdClass
    {
        return empty($value) ? new \stdClass() : $value;
    }

    private function buildAgents(\DateTime $dateDebut, \DateTime $dateFin, ?int $site): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')->from(Agent::class, 'a')
            ->andWhere("a.actif = 'Actif'")
            ->andWhere('a.depart IS NULL OR a.depart >= :debut')
            ->andWhere('a.id > 2') // Exclut les comptes techniques "admin" (1) et "Tout le monde" (2)
            ->setParameter('debut', $dateDebut);

        $agents = $qb->getQuery()->getResult();
        $positionNames = [];

        $result = [];
        foreach ($agents as $agent) {
            $emploiDuTemps = $this->buildEmploiDuTemps($agent, $dateDebut, $dateFin, $site);

            // Un agent sans aucun jour disponible sur la période (aucun jour ne correspond à la
            // bibliothèque demandée, ou aucun horaire déclaré du tout) n'est pas envoyé à l'algorithme.
            if (empty($emploiDuTemps)) {
                continue;
            }

            $quotasPostes = [];
            foreach ((array) $agent->getAlgorithmQuotasByPosition() as $posteId => $quota) {
                if (!isset($positionNames[$posteId])) {
                    $position = $this->entityManager->getRepository(Position::class)->find($posteId);
                    $positionNames[$posteId] = $position ? $position->getName() : null;
                }
                if ($positionNames[$posteId]) {
                    $quotasPostes[$positionNames[$posteId]] = (float) $quota;
                }
            }

            $result[] = [
                // L'id (et non le login) est envoyé à l'algorithme pour anonymiser les agents ;
                // GeneratePlanningMessageHandler reconvertit cet id en agent à la réception de la réponse.
                'nom_agent' => (string) $agent->getId(),
                'quota_pct' => $agent->getAlgorithmQuota() ?? 0,
                'max_sp_journee_pct' => $agent->getAlgorithmMaxDailyQuota() ?? 0,
                'quotas_postes' => $this->asJsonObject($quotasPostes),
                'emploi_du_temps' => $emploiDuTemps,
                'indisponibilites' => $this->asJsonObject($this->buildIndisponibilites($agent, $dateDebut, $dateFin, $site)),
                'pause_inter_listes_min' => $agent->getAlgorithmMinBreakBetweenLists() ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Construit l'emploi du temps déclaré par l'agent (menu "Mon compte" > "Mes heures de présence")
     * sur la période demandée. Si plusieurs emplois du temps se chevauchent sur la période (l'agent a
     * changé d'horaires, ou a une exception ponctuelle), chaque date est résolue avec l'enregistrement
     * qui la couvre spécifiquement.
     *
     * L'appartenance à une bibliothèque n'est pas déterminée par la fiche agent, mais par le site déclaré
     * pour chaque jour dans l'emploi du temps : un jour déclaré pour une autre bibliothèque que celle
     * demandée est simplement absent du résultat pour cette date (l'agent peut rester présent les autres
     * jours). Seul un jour explicitement déclaré "Tout site" (-1) échappe à ce filtre ; un jour sans site
     * défini du tout (ni bibliothèque précise, ni "Tout site") est exclu, quelle que soit la bibliothèque
     * demandée, car son rattachement est indéterminé.
     */
    private function buildEmploiDuTemps(Agent $agent, \DateTime $dateDebut, \DateTime $dateFin, ?int $site): array
    {
        $emploiDuTemps = [];
        $current = clone $dateDebut;

        while ($current <= $dateFin) {
            $periodes = $this->getPeriodsForDate($agent, $current, $site);

            if (!empty($periodes)) {
                $emploiDuTemps[$current->format('d/m/Y')] = $periodes;
            }

            $current->modify('+1 day');
        }

        return $emploiDuTemps;
    }

    /**
     * Résout les horaires de travail déclarés par l'agent pour une date précise (voir buildEmploiDuTemps
     * pour la gestion des chevauchements d'emplois du temps et du filtrage par site). Réutilisé pour
     * calculer l'emploi du temps affiché et pour recadrer les congés sur les heures réellement travaillées.
     */
    private function getPeriodsForDate(Agent $agent, \DateTime $date, ?int $site): array
    {
        $records = $this->entityManager->getRepository(WorkingHour::class)
            ->get($date->format('Y-m-d'), $date->format('Y-m-d'), true, $agent->getId());

        // En cas de chevauchement, une exception (ponctuelle) est prioritaire sur le planning qu'elle remplace.
        usort($records, fn ($a, $b) => $b->getException() <=> $a->getException());

        foreach ($records as $candidate) {
            if ($candidate->getStart() > $date || $candidate->getEnd() < $date) {
                continue;
            }

            $jourIndex = (new \datePl($date->format('Y-m-d'), $candidate->getNumberOfWeeks()))
                ->planning_day_index_for($agent->getId());

            $jour = $candidate->getWorkingHours()[$jourIndex] ?? null;
            $jourSite = $jour[4] ?? null;
            $jourEstTousSites = $jourSite !== null && $jourSite !== '' && (int) $jourSite === -1;
            $jourEstSiteNonDefini = $jourSite === null || $jourSite === '';
            $jourExclu = $site && !$jourEstTousSites && ($jourEstSiteNonDefini || (int) $jourSite !== $site);

            if ($jourExclu) {
                return [];
            }

            return $this->extractPeriods($jour, $candidate->getBreaktime()[$jourIndex] ?? null);
        }

        return [];
    }

    /**
     * Reprend la logique d'extraction des créneaux de App\Command\WorkingHourExportCommand, complétée par
     * la gestion du "temps de pause" (durée) utilisé quand la pause n'est pas déclarée avec une heure de
     * début et de fin explicites (option "pause libre").
     */
    private function extractPeriods(?array $jour, $breaktime = null): array
    {
        if (!$jour) {
            return [];
        }

        $periodes = [];

        // Pause explicite (heure de début et de fin de pause déclarées) : matinée puis après-midi.
        if (!empty($jour[0]) and !empty($jour[1])) {
            $periodes[] = ['debut' => substr($jour[0], 0, 5), 'fin' => substr($jour[1], 0, 5)];
        }
        if (!empty($jour[2]) and !empty($jour[3]) and empty($jour[5] ?? null)) {
            $periodes[] = ['debut' => substr($jour[2], 0, 5), 'fin' => substr($jour[3], 0, 5)];
        }

        // Deux pauses explicites dans la journée.
        if (!empty($jour[2]) and !empty($jour[5] ?? null)) {
            $periodes[] = ['debut' => substr($jour[2], 0, 5), 'fin' => substr($jour[5], 0, 5)];
            $periodes[] = ['debut' => substr($jour[6], 0, 5), 'fin' => substr($jour[3], 0, 5)];
        }

        // Journée continue : aucune heure de début/fin de pause déclarée.
        if (!empty($jour[0]) and empty($jour[2]) and empty($jour[5] ?? null) and !empty($jour[3])) {
            $periode = ['debut' => substr($jour[0], 0, 5), 'fin' => substr($jour[3], 0, 5)];

            // Si un temps de pause (durée) est déclaré séparément (option "pause libre"), on l'ajoute au créneau.
            if (!empty($breaktime)) {
                $periode['temps de pause'] = str_replace('h', ':', HourHelper::decimalToHoursMinutes($breaktime)['as_string']);
            }

            $periodes[] = $periode;
        }

        return $periodes;
    }

    /**
     * Construit les indisponibilités (absences + congés + récupérations) de l'agent sur la période.
     *
     * - "absence" : notée telle quelle (une seule plage par jour couvert), sans recadrage particulier.
     * - "congé" (motif de congé reconnu, ou entrée du module congés/récupérations) : recadré sur les
     *   heures réellement travaillées ce jour-là, en évitant la pause de l'agent — un congé qui englobe
     *   la pause est donc scindé en autant de plages que de périodes de travail qu'il recouvre.
     */
    private function buildIndisponibilites(Agent $agent, \DateTime $dateDebut, \DateTime $dateFin, ?int $site): array
    {
        $indisponibilites = [];

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')->from(Absence::class, 'a')
            ->andWhere('a.perso_id = :agent')
            ->andWhere('a.debut <= :fin')
            ->andWhere('a.fin >= :debut')
            ->andWhere('a.valide > 0')
            ->setParameter('agent', $agent->getId())
            ->setParameter('debut', $dateDebut->format('Y-m-d 00:00:00'))
            ->setParameter('fin', $dateFin->format('Y-m-d 23:59:59'));

        foreach ($qb->getQuery()->getResult() as $absence) {
            $type = $this->estMotifDeConge($absence->getReason()) ? 'congé' : 'absence';
            $this->addIndisponibilite($indisponibilites, $agent, $dateDebut, $dateFin, $absence->getStart(), $absence->getEnd(), $type, $site);
        }

        if (!empty($GLOBALS['config']['Conges-Enable'])) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('h')->from(Holiday::class, 'h')
                ->andWhere('h.perso_id = :agent')
                ->andWhere('h.debut <= :fin')
                ->andWhere('h.fin >= :debut')
                // Inclut les congés/récupérations en attente de validation ; seuls les refusés (valide(_n1) < 0) sont exclus.
                ->andWhere('h.valide >= 0')
                ->andWhere('h.valide_n1 >= 0')
                ->andWhere('h.information = 0')
                ->andWhere('h.supprime = 0')
                ->setParameter('agent', $agent->getId())
                ->setParameter('debut', $dateDebut->format('Y-m-d 00:00:00'))
                ->setParameter('fin', $dateFin->format('Y-m-d 23:59:59'));

            foreach ($qb->getQuery()->getResult() as $conge) {
                $this->addIndisponibilite($indisponibilites, $agent, $dateDebut, $dateFin, $conge->getStart(), $conge->getEnd(), 'congé', $site);
            }
        }

        return $indisponibilites;
    }

    private function estMotifDeConge(?string $motif): bool
    {
        if (!$motif) {
            return false;
        }

        return in_array(mb_strtolower(trim($motif)), self::MOTIFS_CONGE, true);
    }

    /**
     * Découpe [debut, fin] jour par jour sur l'intersection avec [dateDebut, dateFin], puis ajoute une ou
     * plusieurs plages horaires par jour dans $indisponibilites selon le type :
     * - "absence" : la plage du jour est ajoutée telle quelle.
     * - "congé" : la plage du jour est recadrée sur les périodes de travail réelles de l'agent (ce qui
     *   exclut de fait sa pause), et ignorée si elle ne recouvre aucune période travaillée ce jour-là.
     */
    private function addIndisponibilite(array &$indisponibilites, Agent $agent, \DateTime $dateDebut, \DateTime $dateFin, \DateTime $debut, \DateTime $fin, string $type, ?int $site): void
    {
        $current = max(clone $dateDebut, $debut);
        $end = min((clone $dateFin)->setTime(23, 59, 59), $fin);

        while ($current <= $end) {
            $dayStart = $current->format('Y-m-d') === $debut->format('Y-m-d') ? $debut->format('H:i') : '00:00';
            $dayEnd = $current->format('Y-m-d') === $fin->format('Y-m-d') ? $fin->format('H:i') : '23:59';

            if ($type === 'congé') {
                foreach ($this->intersectPeriods($dayStart, $dayEnd, $this->getPeriodsForDate($agent, $current, $site)) as $plage) {
                    $indisponibilites[$current->format('d/m/Y')][] = ['debut' => $plage['debut'], 'fin' => $plage['fin'], 'type' => 'congé'];
                }
            } else {
                $indisponibilites[$current->format('d/m/Y')][] = ['debut' => $dayStart, 'fin' => $dayEnd, 'type' => 'absence'];
            }

            $current->modify('+1 day');
        }
    }

    /**
     * Intersecte la plage [debut, fin] (format "H:i") avec chacune des périodes travaillées données,
     * et retourne une plage par intersection non vide.
     */
    private function intersectPeriods(string $debut, string $fin, array $periodes): array
    {
        $result = [];

        foreach ($periodes as $periode) {
            $s = max($debut, $periode['debut']);
            $e = min($fin, $periode['fin']);

            if ($s < $e) {
                $result[] = ['debut' => $s, 'fin' => $e];
            }
        }

        return $result;
    }
}
