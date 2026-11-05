<?php

namespace App\Tests\Service;

use App\Entity\Absence;
use App\Service\AbsenceReasonLabel;
use PHPUnit\Framework\TestCase;

class AbsenceReasonLabelTest extends TestCase
{
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
        $label = $this->service->format(['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse'], false);

        $this->assertSame(['label' => 'Autre', 'full' => 'Autre', 'truncated' => false], $label);
    }

    public function testDetailsDisplayed(): void
    {
        $label = $this->service->format(['motif' => 'Autre', 'motif_autre' => 'Atelier jeunesse'], true);

        $this->assertSame(['label' => 'Atelier jeunesse', 'full' => 'Atelier jeunesse', 'truncated' => false], $label);
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
}
