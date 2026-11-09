<?php

namespace App\Tests\Twig;

use App\Service\AbsenceReasonLabel;
use App\Twig\AppExtension;
use Tests\PLBWebTestCase;

class AppExtensionAbsenceReasonTest extends PLBWebTestCase
{
    private const DEFAULTS = [
        'Absences-ShowReasonDetails' => '0',
        'Absences-CommentReasons' => '',
        'Absences-CommentDisplay' => 'append',
        'Absences-CommentMaxLength' => '30',
        'Absences-CommentViews' => '["calendar","planning","attendees"]',
    ];

    private AppExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setParams(['Absences-ShowReasonDetails' => '1', 'Absences-CommentReasons' => '']);
    }

    protected function tearDown(): void
    {
        $this->setParams(self::DEFAULTS);

        parent::tearDown();
    }

    /**
     * Sets parameters of the config table and creates a new extension
     * (the service loads the configuration once).
     *
     * @param array<string, string> $params
     */
    private function setParams(array $params): void
    {
        foreach ($params as $name => $value) {
            $this->config->setParam($name, $value);
        }

        $this->extension = new AppExtension(new AbsenceReasonLabel($this->config));
    }

    public function testShortLabelIsEscaped(): void
    {
        $this->assertSame(
            'Atelier &lt;b&gt; &amp; co',
            $this->extension->absenceReason(['motif' => 'Autre', 'motif_autre' => 'Atelier <b> & co'])
        );
    }

    public function testTruncatedLabelHasAccessibleFullText(): void
    {
        $html = $this->extension->absenceReason(['motif' => 'Autre', 'motif_autre' => 'Réunion du réseau des bibliothèques <de> la métropole']);

        $this->assertStringContainsString('tabindex="0"', $html, 'focusable for the keyboard');
        $this->assertStringContainsString('title="Réunion du réseau des bibliothèques &lt;de&gt; la métropole"', $html, 'tooltip');
        $this->assertStringContainsString('<span aria-hidden="true">Réunion du réseau des biblioth…</span>', $html);
        $this->assertStringContainsString('<span class="visually-hidden">Réunion du réseau des bibliothèques &lt;de&gt; la métropole</span>', $html, 'full text for screen readers');
    }

    public function testParameterDisabled(): void
    {
        $this->setParams(['Absences-ShowReasonDetails' => '0']);

        $this->assertSame('Autre', $this->extension->absenceReason(['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse']));
    }

    public function testInvalidValue(): void
    {
        $this->assertSame('', $this->extension->absenceReason(null));
        $this->assertSame('', $this->extension->absenceReason('Autre'));
    }

    // MT52381: comments

    public function testCommentAppend(): void
    {
        $this->setComments('append');

        $this->assertSame('Réunion : COPIL &amp; co', $this->extension->absenceReason(['motif' => 'Réunion', 'commentaires' => 'COPIL & co']));
    }

    public function testCommentReplace(): void
    {
        $this->setComments('replace');

        $this->assertSame('COPIL', $this->extension->absenceReason(['motif' => 'Réunion', 'commentaires' => 'COPIL']));
    }

    public function testCommentIcon(): void
    {
        $this->setComments('icon');

        $html = $this->extension->absenceReason(['motif' => 'Réunion', 'commentaires' => 'COPIL <des> bibliothèques']);

        $this->assertStringStartsWith('Réunion <button type="button"', $html);
        $this->assertStringContainsString('title="COPIL &lt;des&gt; bibliothèques"', $html, 'tooltip, escaped');
        $this->assertStringContainsString('aria-label="Comment: COPIL &lt;des&gt; bibliothèques"', $html, 'accessible name');
        $this->assertStringContainsString('<span aria-hidden="true">ⓘ</span>', $html);
    }

    public function testTruncatedComment(): void
    {
        $this->setComments('append', 10);

        $html = $this->extension->absenceReason(['motif' => 'Réunion', 'commentaires' => 'COPIL des bibliothèques']);

        $this->assertStringStartsWith('Réunion : <span class="absence-comment" tabindex="0" title="COPIL des bibliothèques">', $html);
        $this->assertStringContainsString('<span aria-hidden="true">COPIL des…</span>', $html);
        $this->assertStringContainsString('<span class="visually-hidden">COPIL des bibliothèques</span>', $html);
    }

    public function testCommentViews(): void
    {
        $this->setComments('append', 30, '["calendar"]');
        $absence = ['motif' => 'Réunion', 'commentaires' => 'COPIL'];

        $this->assertSame('Réunion : COPIL', $this->extension->absenceReason($absence, 'calendar'));
        $this->assertSame('Réunion', $this->extension->absenceReason($absence, 'planning'));
        $this->assertSame('Réunion', $this->extension->absenceReason($absence, 'attendees'));
    }

    public function testNoComment(): void
    {
        $this->setComments('append');

        $this->assertSame('Réunion', $this->extension->absenceReason(['motif' => 'Réunion', 'commentaires' => '']));
    }

    private function setComments(string $display, int $maxLength = 30, string $views = '["calendar","planning","attendees"]'): void
    {
        $this->setParams([
            'Absences-CommentReasons' => '[All]',
            'Absences-CommentDisplay' => $display,
            'Absences-CommentMaxLength' => (string) $maxLength,
            'Absences-CommentViews' => $views,
        ]);
    }
}
