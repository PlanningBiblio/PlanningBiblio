<?php

namespace App\Tests\Twig;

use App\Service\AbsenceReasonLabel;
use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;

class AppExtensionAbsenceReasonTest extends TestCase
{
    private AppExtension $extension;
    private mixed $previousParameter;

    protected function setUp(): void
    {
        $this->extension = new AppExtension(new AbsenceReasonLabel());
        $this->previousParameter = $GLOBALS['config']['Absences-ShowReasonDetails'] ?? null;
        $GLOBALS['config']['Absences-ShowReasonDetails'] = 1;
    }

    protected function tearDown(): void
    {
        $GLOBALS['config']['Absences-ShowReasonDetails'] = $this->previousParameter;
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
        $GLOBALS['config']['Absences-ShowReasonDetails'] = 0;

        $this->assertSame('Autre', $this->extension->absenceReason(['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse']));
    }

    public function testInvalidValue(): void
    {
        $this->assertSame('', $this->extension->absenceReason(null));
        $this->assertSame('', $this->extension->absenceReason('Autre'));
    }
}
