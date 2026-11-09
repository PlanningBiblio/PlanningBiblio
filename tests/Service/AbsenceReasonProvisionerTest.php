<?php

namespace App\Tests\Service;

use App\Service\AbsenceReasonProvisioner;
use PHPUnit\Framework\TestCase;

class AbsenceReasonProvisionerTest extends TestCase
{
    private const EXISTING = ['Congé', 'Autre', 'Agenda personnel'];

    public function testNothingToCreateWhenNoImportIsConfigured(): void
    {
        $config = [
            'ICS-Pattern1' => 'Calendrier',
            'MSGraph-AbsenceReason' => 'Office 365',
            'Hamac-motif' => 'Hamac',
        ];

        $this->assertSame([], AbsenceReasonProvisioner::reasonsToCreate($config, self::EXISTING), 'reasons of unused imports are not created');
    }

    public function testConfiguredReasonsOfConfiguredImports(): void
    {
        $config = [
            'ICS-Server1' => 'https://example.com/[login].ics',
            'ICS-Pattern1' => ' Calendrier ',
            'ICS-Server3' => '1',
            'ICS-Pattern3' => 'Agenda personnel',
            'MSGraph-ClientID' => 'client',
            'MSGraph-TenantID' => 'tenant',
            'MSGraph-AbsenceReason' => 'Office 365',
            'Hamac-csv' => '/data/hamac.csv',
            'Hamac-motif' => 'Hamac',
            'AbsImport-Reason' => 'Import RH',
        ];

        $this->assertSame(
            ['Calendrier', 'Office 365', 'Hamac', 'Import RH'],
            AbsenceReasonProvisioner::reasonsToCreate($config, self::EXISTING)
        );
    }

    public function testKeywordsAndExistingReasonsAreIgnored(): void
    {
        $config = [
            'ICS-Server1' => 'https://example.com/[login].ics',
            'ICS-Pattern1' => '[SUMMARY]',
            'ICS-Server2' => 'https://example.com/other.ics',
            'ICS-Pattern2' => 'congé',
            'MSGraph-ClientID' => 'client',
            'MSGraph-TenantID' => 'tenant',
            'MSGraph-AbsenceReason' => '[ANY_FUTURE_KEYWORD]',
        ];

        $this->assertSame([], AbsenceReasonProvisioner::reasonsToCreate($config, self::EXISTING));
    }

    public function testDefaultReasonsOfConfiguredImports(): void
    {
        $config = [
            'MSGraph-ClientID' => 'client',
            'MSGraph-TenantID' => 'tenant',
            'MSGraph-AbsenceReason' => '',
            'Hamac-csv' => '/data/hamac.csv',
            'Hamac-motif' => '  ',
        ];

        $this->assertSame(['Outlook', 'Hamac'], AbsenceReasonProvisioner::reasonsToCreate($config, self::EXISTING));
    }

    public function testNoDuplicates(): void
    {
        $config = [
            'ICS-Server1' => 'https://example.com/a.ics',
            'ICS-Pattern1' => 'Calendrier',
            'ICS-Server2' => 'https://example.com/b.ics',
            'ICS-Pattern2' => 'CALENDRIER',
        ];

        $this->assertSame(['Calendrier'], AbsenceReasonProvisioner::reasonsToCreate($config, self::EXISTING));
    }

    public function testEmptyPatternIsIgnored(): void
    {
        $config = ['ICS-Server1' => 'https://example.com/a.ics', 'ICS-Pattern1' => ''];

        $this->assertSame([], AbsenceReasonProvisioner::reasonsToCreate($config, self::EXISTING));
    }
}
