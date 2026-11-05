<?php

namespace App\Service;

use App\Entity\Absence;

/**
 * Label of an absence reason displayed in the calendars and in the absence lists.
 *
 * If the parameter Absences-ShowReasonDetails is enabled and the absence has reason details
 * (field "motif_autre": details typed with the reason "Autre", or title of an imported event
 * that matches no reason), these details are displayed instead of the reason.
 * Reason details longer than MAX_LENGTH characters are truncated; the full text is returned too.
 * The reasons themselves are displayed as before (never truncated).
 */
class AbsenceReasonLabel
{
    public const MAX_LENGTH = 30;
    public const ELLIPSIS = '…';

    public function isDetailsDisplayEnabled(): bool
    {
        return !empty($GLOBALS['config']['Absences-ShowReasonDetails']);
    }

    /**
     * @param array<string, mixed>|Absence $absence     Absence (entity, or array from the legacy queries: keys motif, motif_autre)
     * @param bool|null                    $showDetails Overrides the parameter Absences-ShowReasonDetails (tests)
     *
     * @return array{label: string, full: string, truncated: bool}
     */
    public function format(array|Absence $absence, ?bool $showDetails = null): array
    {
        $showDetails ??= $this->isDetailsDisplayEnabled();

        if ($absence instanceof Absence) {
            $reason = (string) $absence->getReason();
            $details = (string) $absence->getOtherReason();
        } else {
            $reason = (string) ($absence['motif'] ?? '');
            $details = (string) ($absence['motif_autre'] ?? '');
        }

        $details = trim((string) preg_replace('/\s+/u', ' ', $details));

        if (!$showDetails || $details === '') {
            return ['label' => $reason, 'full' => $reason, 'truncated' => false];
        }

        if (mb_strlen($details, 'UTF-8') <= self::MAX_LENGTH) {
            return ['label' => $details, 'full' => $details, 'truncated' => false];
        }

        return [
            'label' => rtrim(mb_substr($details, 0, self::MAX_LENGTH, 'UTF-8')) . self::ELLIPSIS,
            'full' => $details,
            'truncated' => true,
        ];
    }
}
