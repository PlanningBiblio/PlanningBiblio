<?php

namespace App\Tests\Service;

use App\Entity\Absence;
use App\Service\AbsenceReasonLabel;
use PHPUnit\Framework\TestCase;

class AbsenceReasonLabelTest extends TestCase
{
    private const NO_COMMENT = ['reasons' => ''];

    private AbsenceReasonLabel $service;
    private mixed $previousParameter;

    protected function setUp(): void
    {
        $this->service = new AbsenceReasonLabel();
        $this->previousParameter = $GLOBALS['config']['Absences-ShowReasonDetails'] ?? null;
    }

    protected function tearDown(): void
    {
        $GLOBALS['config']['Absences-ShowReasonDetails'] = $this->previousParameter;
    }

    public function testDetailsHiddenByDefault(): void
    {
        $label = $this->service->format(['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse'], false, null, self::NO_COMMENT);

        $this->assertSame(['label' => 'Autre', 'full' => 'Autre', 'truncated' => false], $this->reason($label));
        $this->assertNull($label['comment']);
    }

    public function testDetailsDisplayed(): void
    {
        $label = $this->service->format(['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse'], true, null, self::NO_COMMENT);

        $this->assertSame(['label' => 'Atelier jeunesse', 'full' => 'Atelier jeunesse', 'truncated' => false], $this->reason($label));
    }

    public function testReasonWithoutDetails(): void
    {
        $this->assertSame('Congé', $this->service->format(['motif' => 'Congé', 'motif_autre' => ''], true)['label']);
        $this->assertSame('Congé', $this->service->format(['motif' => 'Congé', 'motif_autre' => '   '], true)['label']);
        $this->assertSame('Congé payé', $this->service->format(['motif' => 'Congé payé'], true)['label'], 'holidays have no motif_autre');
    }

    public function testLongDetailsAreTruncated(): void
    {
        $details = "Réunion du réseau des bibliothèques\nde la métropole";
        $label = $this->service->format(['motif' => 'Autre', 'motif_autre' => $details], true);

        $this->assertTrue($label['truncated']);
        $this->assertSame('Réunion du réseau des bibliothèques de la métropole', $label['full'], 'line breaks replaced by spaces');
        $this->assertSame('Réunion du réseau des biblioth…', $label['label']);
        $this->assertSame(AbsenceReasonLabel::MAX_LENGTH + 1, mb_strlen($label['label']), '30 characters + ellipsis (multibyte safe)');
    }

    public function testLongReasonIsNotTruncated(): void
    {
        $reason = 'Formation professionnelle continue obligatoire';
        $label = $this->service->format(['motif' => $reason, 'motif_autre' => ''], true);

        $this->assertSame($reason, $label['label'], 'reasons are displayed as before');
        $this->assertFalse($label['truncated']);
    }

    public function testEntity(): void
    {
        $absence = new Absence();
        $absence->setReason('Autre');
        $absence->setOtherReason('Atelier jeunesse');

        $this->assertSame('Atelier jeunesse', $this->service->format($absence, true)['label']);
        $this->assertSame('Autre', $this->service->format($absence, false)['label']);
    }

    public function testParameter(): void
    {
        $absence = ['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse'];

        $GLOBALS['config']['Absences-ShowReasonDetails'] = 0;
        $this->assertSame('Autre', $this->service->format($absence)['label']);

        $GLOBALS['config']['Absences-ShowReasonDetails'] = 1;
        $this->assertSame('Atelier jeunesse', $this->service->format($absence)['label']);
    }

    // MT52381: comments

    public function testParseReasons(): void
    {
        $this->assertSame(['réunion', 'formation', 'autre'], AbsenceReasonLabel::parseReasons(' Réunion ; formation;;AUTRE ; réunion'));
        $this->assertSame(['réunion', 'formation'], AbsenceReasonLabel::parseReasons("Réunion<br/>Formation"), 'line breaks of the textarea');
        $this->assertSame(['[all]'], AbsenceReasonLabel::parseReasons('[All]'));
        $this->assertSame([], AbsenceReasonLabel::parseReasons('  '));
    }

    public function testNoCommentByDefault(): void
    {
        $label = $this->service->format(['motif' => 'Réunion', 'commentaires' => 'COPIL'], false, null, self::NO_COMMENT);

        $this->assertNull($label['comment']);
    }

    public function testCommentForListedReason(): void
    {
        $settings = ['reasons' => 'réunion;Formation'];

        $label = $this->service->format(['motif' => 'Réunion', 'commentaires' => 'COPIL'], false, null, $settings);
        $this->assertSame(['label' => 'COPIL', 'full' => 'COPIL', 'truncated' => false], $label['comment'], 'case-insensitive');
        $this->assertSame('Réunion', $label['label'], 'the reason is unchanged');
        $this->assertSame('append', $label['display'], 'default display');

        $label = $this->service->format(['motif' => 'Congé payé', 'commentaires' => 'Vacances'], false, null, $settings);
        $this->assertNull($label['comment'], 'reason not listed');
    }

    public function testCommentForAllReasons(): void
    {
        $label = $this->service->format(['motif' => 'Congé payé', 'commentaires' => 'Matin'], false, null, ['reasons' => '[All]']);

        $this->assertSame('Matin', $label['comment']['label']);
    }

    public function testEmptyComment(): void
    {
        foreach (['', '   ', '<br/>', null] as $comment) {
            $label = $this->service->format(['motif' => 'Réunion', 'commentaires' => $comment], false, null, ['reasons' => '[All]']);
            $this->assertNull($label['comment'], var_export($comment, true));
        }
    }

    public function testCommentFirstLineAndTruncation(): void
    {
        $settings = ['reasons' => '[All]', 'maxLength' => 10];

        $label = $this->service->format(['motif' => 'Autre', 'commentaires' => "Atelier jeunesse<br/>\nAnimation au bassin Nord"], false, null, $settings);
        $this->assertSame('Atelier je…', $label['comment']['label'], 'first line, truncated to 10 characters');
        $this->assertSame('Atelier jeunesse Animation au bassin Nord', $label['comment']['full'], 'full comment on one line');
        $this->assertTrue($label['comment']['truncated']);

        $label = $this->service->format(['motif' => 'Autre', 'commentaires' => "COPIL\nOrdre du jour"], false, null, $settings);
        $this->assertSame('COPIL …', $label['comment']['label'], 'short first line followed by other lines');
        $this->assertTrue($label['comment']['truncated']);

        $label = $this->service->format(['motif' => 'Autre', 'commentaires' => '<b>COPIL</b>'], false, null, $settings);
        $this->assertSame('COPIL', $label['comment']['label'], 'tags removed');
    }

    public function testCommentViews(): void
    {
        $settings = ['reasons' => '[All]', 'views' => ['calendar']];
        $absence = ['motif' => 'Réunion', 'commentaires' => 'COPIL'];

        $this->assertNotNull($this->service->format($absence, false, AbsenceReasonLabel::VIEW_CALENDAR, $settings)['comment']);
        $this->assertNull($this->service->format($absence, false, AbsenceReasonLabel::VIEW_PLANNING, $settings)['comment']);
        $this->assertNull($this->service->format($absence, false, AbsenceReasonLabel::VIEW_ATTENDEES, $settings)['comment']);
        $this->assertNull($this->service->format($absence, false, AbsenceReasonLabel::VIEW_CALENDAR, ['reasons' => '[All]', 'views' => []])['comment'], 'no screen checked');
    }

    public function testCommentWithReasonDetails(): void
    {
        // The comment rule uses the reason ("Autre"), the label uses the reason details
        $label = $this->service->format(['motif' => 'Autre', 'motif_autre' => 'Atelier', 'commentaires' => 'Bassin Nord'], true, null, ['reasons' => 'Autre']);

        $this->assertSame('Atelier', $label['label']);
        $this->assertSame('Bassin Nord', $label['comment']['label']);
    }

    public function testSettingsFromConfiguration(): void
    {
        $previous = $GLOBALS['config'] ?? [];
        $GLOBALS['config']['Absences-CommentReasons'] = 'Réunion';
        $GLOBALS['config']['Absences-CommentDisplay'] = 'icon';
        $GLOBALS['config']['Absences-CommentMaxLength'] = '12';
        $GLOBALS['config']['Absences-CommentViews'] = '["planning"]';

        try {
            $this->assertSame(
                ['reasons' => 'Réunion', 'display' => 'icon', 'maxLength' => 12, 'views' => ['planning']],
                $this->service->commentSettings()
            );

            $GLOBALS['config']['Absences-CommentDisplay'] = 'unknown';
            $GLOBALS['config']['Absences-CommentMaxLength'] = '0';
            unset($GLOBALS['config']['Absences-CommentViews']);
            $settings = $this->service->commentSettings();
            $this->assertSame('append', $settings['display'], 'invalid display: default');
            $this->assertSame(30, $settings['maxLength'], 'invalid length: default');
            $this->assertSame(AbsenceReasonLabel::VIEWS, $settings['views'], 'parameter missing: all the screens');
        } finally {
            $GLOBALS['config'] = $previous;
        }
    }

    /**
     * @param array<string, mixed> $label
     *
     * @return array{label: string, full: string, truncated: bool}
     */
    private function reason(array $label): array
    {
        return ['label' => $label['label'], 'full' => $label['full'], 'truncated' => $label['truncated']];
    }
}
