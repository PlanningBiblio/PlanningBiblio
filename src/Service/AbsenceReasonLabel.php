<?php

namespace App\Service;

use App\Entity\Absence;
use App\Repository\ConfigRepository;

/**
 * Label of an absence reason displayed in the calendars and in the absence lists.
 *
 * 1. Reason: if the parameter Absences-ShowReasonDetails is enabled and the absence has reason details
 *    (field "motif_autre": details typed with the reason "Autre", or title of an imported event that
 *    matches no reason), these details are displayed instead of the reason. Reason details longer than
 *    MAX_LENGTH characters are truncated; the full text is returned too. Reasons are never truncated.
 *
 * 2. Comment (MT52381): if the reason of the absence is listed in Absences-CommentReasons (or "[All]"),
 *    the comment of the absence is displayed according to Absences-CommentDisplay:
 *    - "append": after the reason ("Réunion : COPIL");
 *    - "replace": instead of the reason ("COPIL");
 *    - "icon": a symbol after the reason, the comment being displayed in a tooltip.
 *    The first line of the comment is displayed, truncated to Absences-CommentMaxLength characters.
 *    Absences-CommentViews restricts the screens where comments are displayed.
 */
class AbsenceReasonLabel
{
    public const MAX_LENGTH = 30;
    public const ELLIPSIS = '…';

    public const ALL_REASONS = '[All]';

    public const DISPLAY_APPEND = 'append';
    public const DISPLAY_REPLACE = 'replace';
    public const DISPLAY_ICON = 'icon';
    public const DISPLAY_MODES = [self::DISPLAY_APPEND, self::DISPLAY_REPLACE, self::DISPLAY_ICON];

    public const VIEW_CALENDAR = 'calendar';
    public const VIEW_PLANNING = 'planning';
    public const VIEW_ATTENDEES = 'attendees';
    public const VIEWS = [self::VIEW_CALENDAR, self::VIEW_PLANNING, self::VIEW_ATTENDEES];

    /**
     * Configuration values, loaded once per instance (one query per request): the filter may be
     * called for each absence of a page.
     *
     * @var array<string, mixed>|null
     */
    private ?array $config = null;

    public function __construct(private ConfigRepository $configRepository)
    {
    }

    public function isDetailsDisplayEnabled(): bool
    {
        return !empty($this->config('Absences-ShowReasonDetails'));
    }

    /**
     * Comment settings, read from the configuration.
     *
     * @return array{reasons: string, display: string, maxLength: int, views: string[]}
     */
    public function commentSettings(): array
    {
        $views = $this->config('Absences-CommentViews');
        if (is_string($views)) {
            $decoded = json_decode($views, true);
            $views = is_array($decoded) ? $decoded : null;
        }

        return self::normalizeSettings([
            'reasons' => (string) ($this->config('Absences-CommentReasons') ?? ''),
            'display' => (string) ($this->config('Absences-CommentDisplay') ?? self::DISPLAY_APPEND),
            'maxLength' => (int) ($this->config('Absences-CommentMaxLength') ?? self::MAX_LENGTH),
            // Parameter missing (option not installed): all the screens
            'views' => is_array($views) ? $views : self::VIEWS,
        ]);
    }

    /**
     * Value of a configuration parameter (config table), null if it does not exist.
     */
    private function config(string $name): mixed
    {
        $this->config ??= $this->configRepository->getAll();

        return $this->config[$name] ?? null;
    }

    /**
     * @param array<string, mixed>|Absence $absence     Absence (entity, or array from the legacy queries:
     *                                                  keys motif, motif_autre, commentaires)
     * @param bool|null                    $showDetails Overrides the parameter Absences-ShowReasonDetails (tests)
     * @param string|null                  $view        Screen (VIEW_*); null: the comment is displayed on any screen
     * @param array<string, mixed>|null    $settings    Overrides the comment settings (tests)
     *
     * @return array{
     *     label: string, full: string, truncated: bool,
     *     comment: array{label: string, full: string, truncated: bool}|null,
     *     display: string
     * }
     */
    public function format(array|Absence $absence, ?bool $showDetails = null, ?string $view = null, ?array $settings = null): array
    {
        $showDetails ??= $this->isDetailsDisplayEnabled();
        $settings = $settings !== null ? self::normalizeSettings($settings) : $this->commentSettings();

        if ($absence instanceof Absence) {
            $reason = (string) $absence->getReason();
            $details = (string) $absence->getOtherReason();
            $comment = (string) $absence->getComment();
        } else {
            $reason = (string) ($absence['motif'] ?? '');
            $details = (string) ($absence['motif_autre'] ?? '');
            $comment = (string) ($absence['commentaires'] ?? '');
        }

        $result = $this->reasonLabel($reason, $details, $showDetails);
        $result['comment'] = null;
        $result['display'] = $settings['display'];

        if ($this->isCommentDisplayed($reason, $view, $settings)) {
            $result['comment'] = $this->commentLabel($comment, $settings['maxLength']);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return array{reasons: string, display: string, maxLength: int, views: string[]}
     */
    public static function normalizeSettings(array $settings): array
    {
        $display = (string) ($settings['display'] ?? self::DISPLAY_APPEND);
        $maxLength = (int) ($settings['maxLength'] ?? self::MAX_LENGTH);

        return [
            'reasons' => (string) ($settings['reasons'] ?? ''),
            'display' => in_array($display, self::DISPLAY_MODES, true) ? $display : self::DISPLAY_APPEND,
            'maxLength' => $maxLength > 0 ? $maxLength : self::MAX_LENGTH,
            'views' => array_values(array_map('strval', (array) ($settings['views'] ?? self::VIEWS))),
        ];
    }

    /**
     * Reasons listed in Absences-CommentReasons: separated by semicolons (line breaks accepted).
     *
     * @return string[] Normalized reasons, or ['[all]']
     */
    public static function parseReasons(string $value): array
    {
        $values = preg_split('/;|<br\s*\/?>|\R/i', $value) ?: [];
        $values = array_map([ImportedAbsenceReason::class, 'normalize'], $values);

        return array_values(array_unique(array_filter($values, fn (string $v) => $v !== '')));
    }

    /**
     * @param array{reasons: string, display: string, maxLength: int, views: string[]} $settings
     */
    private function isCommentDisplayed(string $reason, ?string $view, array $settings): bool
    {
        if ($view !== null && !in_array($view, $settings['views'], true)) {
            return false;
        }

        $reasons = self::parseReasons($settings['reasons']);

        if (empty($reasons)) {
            return false;
        }

        return in_array(ImportedAbsenceReason::normalize(self::ALL_REASONS), $reasons, true)
            || in_array(ImportedAbsenceReason::normalize($reason), $reasons, true);
    }

    /**
     * @return array{label: string, full: string, truncated: bool}
     */
    private function reasonLabel(string $reason, string $details, bool $showDetails): array
    {
        $details = trim((string) preg_replace('/\s+/u', ' ', $details));

        if (!$showDetails || $details === '') {
            return ['label' => $reason, 'full' => $reason, 'truncated' => false];
        }

        return $this->truncate($details, self::MAX_LENGTH);
    }

    /**
     * First line of the comment, truncated; the full comment on one line.
     *
     * @return array{label: string, full: string, truncated: bool}|null Null if the comment is empty
     */
    private function commentLabel(string $comment, int $maxLength): ?array
    {
        $lines = preg_split('/<br\s*\/?>|\R/i', $comment) ?: [];
        $lines = array_values(array_filter(
            array_map(fn (string $line) => trim((string) preg_replace('/\s+/u', ' ', strip_tags($line))), $lines),
            fn (string $line) => $line !== ''
        ));

        if (empty($lines)) {
            return null;
        }

        $full = implode(' ', $lines);
        $first = $lines[0];

        $label = $this->truncate($first, $maxLength);
        $label['full'] = $full;

        // Other lines (e.g. description of an imported event): ellipsis, full comment in the tooltip
        if (!$label['truncated'] && $first !== $full) {
            $label['label'] .= ' ' . self::ELLIPSIS;
            $label['truncated'] = true;
        }

        return $label;
    }

    /**
     * @return array{label: string, full: string, truncated: bool}
     */
    private function truncate(string $text, int $maxLength): array
    {
        if (mb_strlen($text, 'UTF-8') <= $maxLength) {
            return ['label' => $text, 'full' => $text, 'truncated' => false];
        }

        return [
            'label' => rtrim(mb_substr($text, 0, $maxLength, 'UTF-8')) . self::ELLIPSIS,
            'full' => $text,
            'truncated' => true,
        ];
    }
}
