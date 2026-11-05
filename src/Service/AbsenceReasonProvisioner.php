<?php

namespace App\Service;

use App\Entity\AbsenceReason;
use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Creates the absence reasons defined in the configuration of the imports
 * (ICS-PatternX, MSGraph-AbsenceReason, Hamac-motif, AbsImport-Reason), so that the
 * imported absences always find their reason in the list of reasons.
 *
 * - Empty values and keywords between square brackets ("[SUMMARY]"...) are ignored.
 * - A reason is only created for an import that is configured; when the parameter is empty,
 *   the default reason used by the import is created ("Outlook" for Microsoft Graph, "Hamac" for
 *   the CSV import: default value of AbsenceImportCSVCommand, unchanged).
 * - Existing reasons are compared like the imports do (trimmed, case-insensitive).
 */
class AbsenceReasonProvisioner
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Pure function, also used by the migration that provisions the existing instances.
     *
     * @param array<string, mixed> $config          Configuration values, by name
     * @param string[]             $existingReasons Labels of the existing reasons
     *
     * @return string[] Labels of the reasons to create
     */
    public static function reasonsToCreate(array $config, array $existingReasons): array
    {
        $value = fn (string $name): string => trim((string) ($config[$name] ?? ''));

        $sources = [
            ['ICS-Pattern1', $value('ICS-Server1') !== '', null],
            ['ICS-Pattern2', $value('ICS-Server2') !== '', null],
            ['ICS-Pattern3', !empty($config['ICS-Server3']), null],
            ['MSGraph-AbsenceReason', $value('MSGraph-ClientID') !== '' && $value('MSGraph-TenantID') !== '', 'Outlook'],
            ['Hamac-motif', $value('Hamac-csv') !== '', 'Hamac'],
            ['AbsImport-Reason', true, null],
        ];

        $known = array_map([ImportedAbsenceReason::class, 'normalize'], $existingReasons);
        $toCreate = [];

        foreach ($sources as [$param, $enabled, $default]) {
            if (!$enabled) {
                continue;
            }

            $reason = $value($param) !== '' ? $value($param) : (string) $default;

            if ($reason === '' || ImportedAbsenceReason::isKeyword($reason)) {
                continue;
            }

            $normalized = ImportedAbsenceReason::normalize($reason);
            if (in_array($normalized, $known, true)) {
                continue;
            }

            $known[] = $normalized;
            $toCreate[] = $reason;
        }

        return $toCreate;
    }

    /**
     * Creates the missing reasons, at the end of the list.
     *
     * @return string[] Labels of the created reasons
     */
    public function provision(): array
    {
        $config = $this->entityManager->getRepository(Config::class)->getAll();
        $reasons = $this->entityManager->getRepository(AbsenceReason::class)->findAll();

        $toCreate = self::reasonsToCreate($config, array_map(fn (AbsenceReason $r) => $r->getValue(), $reasons));

        if (empty($toCreate)) {
            return [];
        }

        $rank = array_reduce($reasons, fn (int $max, AbsenceReason $r) => max($max, $r->getRank()), 0);

        foreach ($toCreate as $label) {
            $reason = new AbsenceReason();
            $reason->setValue($label);
            $reason->setRank(++$rank);
            $reason->setType(0);
            $reason->setNotificationWorkflow('A');
            $reason->setTeleworking(false);
            $this->entityManager->persist($reason);
        }

        $this->entityManager->flush();

        return $toCreate;
    }
}
