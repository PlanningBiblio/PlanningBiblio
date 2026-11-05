<?php

namespace App\Tests\Service;

use App\Service\ImportedAbsenceReason;
use PHPUnit\Framework\TestCase;

class ImportedAbsenceReasonTest extends TestCase
{
    private const REASONS = ['Télétravail', 'Congé', 'Agenda personnel', 'Autre', 'Été'];

    private ImportedAbsenceReason $service;

    protected function setUp(): void
    {
        $this->service = new ImportedAbsenceReason();
    }

    public function testIsKeyword(): void
    {
        $this->assertTrue(ImportedAbsenceReason::isKeyword('[SUMMARY]'));
        $this->assertTrue(ImportedAbsenceReason::isKeyword(' [ANY_FUTURE_KEYWORD] '));
        $this->assertFalse(ImportedAbsenceReason::isKeyword('Agenda personnel'));
        $this->assertFalse(ImportedAbsenceReason::isKeyword('[Agenda'));
        $this->assertFalse(ImportedAbsenceReason::isKeyword(''));
        $this->assertFalse(ImportedAbsenceReason::isKeyword(null));
    }

    public function testFindReasonIsTrimmedAndCaseInsensitive(): void
    {
        $this->assertSame('Télétravail', $this->service->findReason('  télétravail ', self::REASONS));
        $this->assertSame('Télétravail', $this->service->findReason('TÉLÉTRAVAIL', self::REASONS), 'uppercase accented letters (mb_strtolower)');
        $this->assertSame('Été', $this->service->findReason('été', self::REASONS));
        $this->assertNull($this->service->findReason('Télétravail matin', self::REASONS));
        $this->assertNull($this->service->findReason('', self::REASONS));
    }

    public function testSummaryMatchingReason(): void
    {
        $this->assertSame(
            ['reason' => 'Télétravail', 'otherReason' => ''],
            $this->service->resolve('[SUMMARY]', ' télétravail ', self::REASONS)
        );
    }

    public function testSummaryWithoutMatchingReason(): void
    {
        $this->assertSame(
            ['reason' => 'Autre', 'otherReason' => 'Atelier jeunesse'],
            $this->service->resolve('[SUMMARY]', ' Atelier jeunesse ', self::REASONS)
        );
    }

    public function testConfiguredReason(): void
    {
        $this->assertSame(
            ['reason' => 'Agenda personnel', 'otherReason' => ''],
            $this->service->resolve('agenda personnel', 'Réunion', self::REASONS),
            'existing configured reason: exact label, title not copied'
        );

        $this->assertSame(
            ['reason' => 'Autre', 'otherReason' => 'Réunion'],
            $this->service->resolve('Outlook', 'Réunion', self::REASONS),
            'configured reason that does not exist: "Autre" + title'
        );

        $this->assertSame(
            ['reason' => 'Autre', 'otherReason' => ''],
            $this->service->resolve('Autre', 'Réunion', self::REASONS),
            'configured reason "Autre" (existing): unchanged behaviour'
        );
    }

    public function testIcsEvents(): void
    {
        $this->assertSame('Réunion', $this->service->icsEventTitle(['SUMMARY' => ' Réunion ']));
        $this->assertSame('[Empty]', $this->service->icsEventTitle(['SUMMARY' => '  ']));
        $this->assertSame('[Empty]', $this->service->icsEventTitle([]));

        foreach (['PRIVATE', 'private', 'CONFIDENTIAL'] as $class) {
            $event = ['SUMMARY' => 'Médecin', 'CLASS' => $class];
            $this->assertTrue($this->service->isPrivateIcsEvent($event), $class);
            $this->assertSame('[Private]', $this->service->icsEventTitle($event), $class);
        }

        $this->assertFalse($this->service->isPrivateIcsEvent(['CLASS' => 'PUBLIC']));
        $this->assertFalse($this->service->isPrivateIcsEvent([]));
    }

    public function testGraphEvents(): void
    {
        $this->assertSame('Réunion', $this->service->graphEventTitle((object) ['subject' => 'Réunion', 'sensitivity' => 'normal']));
        $this->assertSame('[Empty]', $this->service->graphEventTitle((object) ['subject' => null, 'sensitivity' => 'normal']));
        $this->assertSame('[Private]', $this->service->graphEventTitle((object) ['subject' => 'Médecin', 'sensitivity' => 'private']));
        $this->assertSame('[Private]', $this->service->graphEventTitle((object) ['subject' => 'Médecin', 'sensitivity' => 'confidential']));
        $this->assertSame('Médecin', $this->service->graphEventTitle((object) ['subject' => 'Médecin', 'sensitivity' => 'personal']), 'personal is not hidden');
    }

    public function testPrivateEventWithSummary(): void
    {
        $title = $this->service->icsEventTitle(['SUMMARY' => 'Télétravail', 'CLASS' => 'PRIVATE']);

        $this->assertSame(
            ['reason' => 'Autre', 'otherReason' => '[Private]'],
            $this->service->resolve('[SUMMARY]', $title, self::REASONS),
            'the title of a private event is never used'
        );
    }
}
