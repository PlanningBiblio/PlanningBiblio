<?php

namespace App\Service;

/**
 * Reason of the absences imported from calendars or files (CSV, ICS, Microsoft Graph).
 *
 * Rule shared by the imports:
 * - configured reason "[SUMMARY]": the title of the event is used as reason if it matches
 *   an existing reason, otherwise the reason is "Autre" and the title goes to "motif_autre";
 * - other configured reason: used if it exists, otherwise "Autre" with the title in "motif_autre".
 *
 * Reasons are compared without leading and trailing spaces, case-insensitively (UTF-8).
 * The reason saved is the exact label of the Planno reason.
 *
 * Private events: the title is replaced by "[Private]" (and their description must not be imported).
 * Events without title: "[Empty]".
 */
class ImportedAbsenceReason
{
    public const OTHER_REASON = 'Autre';
    public const SUMMARY_KEYWORD = '[SUMMARY]';
    public const PRIVATE_TITLE = '[Private]';
    public const EMPTY_TITLE = '[Empty]';

    private const ICS_PRIVATE_CLASSES = ['PRIVATE', 'CONFIDENTIAL'];
    private const GRAPH_PRIVATE_SENSITIVITIES = ['private', 'confidential'];

    /**
     * Returns true for keywords such as "[SUMMARY]": values between square brackets are not reasons.
     */
    public static function isKeyword(?string $value): bool
    {
        return (bool) preg_match('/^\[.*\]$/s', trim((string) $value));
    }

    /**
     * Normalized form used to compare reasons: no leading and trailing spaces, lowercase (UTF-8).
     */
    public static function normalize(?string $value): string
    {
        return mb_strtolower(trim((string) $value), 'UTF-8');
    }

    /**
     * Returns the exact label of the reason matching $value, or null.
     *
     * @param string[] $reasons Labels of the existing reasons
     */
    public function findReason(?string $value, array $reasons): ?string
    {
        $value = self::normalize($value);

        if ($value === '') {
            return null;
        }

        foreach ($reasons as $reason) {
            if (self::normalize($reason) === $value) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * @param string   $configuredReason Value of the parameter (ICS-PatternX, MSGraph-AbsenceReason, Hamac-motif)
     * @param string   $title            Title of the event (already replaced for private or empty events)
     * @param string[] $reasons          Labels of the existing reasons
     *
     * @return array{reason: string, otherReason: string}
     */
    public function resolve(string $configuredReason, string $title, array $reasons): array
    {
        $title = trim($title);
        $searched = trim($configuredReason) === self::SUMMARY_KEYWORD ? $title : $configuredReason;

        $reason = $this->findReason($searched, $reasons);

        if ($reason !== null) {
            return ['reason' => $reason, 'otherReason' => ''];
        }

        return ['reason' => self::OTHER_REASON, 'otherReason' => $title];
    }

    /**
     * @param array<string, mixed> $event Event parsed by the ICS parser (keys SUMMARY, CLASS, ...)
     */
    public function isPrivateIcsEvent(array $event): bool
    {
        return in_array(strtoupper(trim((string) ($event['CLASS'] ?? ''))), self::ICS_PRIVATE_CLASSES, true);
    }

    /**
     * @param array<string, mixed> $event Event parsed by the ICS parser
     */
    public function icsEventTitle(array $event): string
    {
        if ($this->isPrivateIcsEvent($event)) {
            return self::PRIVATE_TITLE;
        }

        $title = trim((string) ($event['SUMMARY'] ?? ''));

        return $title !== '' ? $title : self::EMPTY_TITLE;
    }

    /**
     * @param object $event Event returned by Microsoft Graph (properties sensitivity, subject)
     */
    public function isPrivateGraphEvent(object $event): bool
    {
        return in_array(strtolower(trim((string) ($event->sensitivity ?? ''))), self::GRAPH_PRIVATE_SENSITIVITIES, true);
    }

    /**
     * @param object $event Event returned by Microsoft Graph
     */
    public function graphEventTitle(object $event): string
    {
        if ($this->isPrivateGraphEvent($event)) {
            return self::PRIVATE_TITLE;
        }

        $title = trim((string) ($event->subject ?? ''));

        return $title !== '' ? $title : self::EMPTY_TITLE;
    }
}
