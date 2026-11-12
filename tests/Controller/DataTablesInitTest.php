<?php

use App\Entity\Agent;
use Tests\PLBWebTestCase;

/**
 * Automatic initialization of the CJDataTable tables (public/js/CJScript.js), in a browser.
 * Table used: list of agents (/agent), with a dataTableNoSort column.
 */
class DataTablesInitTest extends PLBWebTestCase
{
    public function testCJDataTable(): void
    {
        $this->builder->delete(Agent::class);

        $admin = $this->builder->build(Agent::class, [
            'login' => 'dtadmin', 'nom' => 'Admin', 'prenom' => 'Data',
            'droits' => [21, 4, 99, 100], 'supprime' => 0, 'actif' => 'Actif',
        ]);

        for ($i = 1; $i <= 30; $i++) {
            $this->builder->build(Agent::class, [
                'login' => 'dtagent' . $i, 'nom' => sprintf('Agent%02d', $i), 'prenom' => 'Test',
                'supprime' => 0, 'actif' => 'Actif',
            ]);
        }

        $this->setUpPantherClient();

        $_SESSION['login_id'] = '';
        $_SESSION['login_nom'] = '';
        $_SESSION['login_prenom'] = '';
        $_SESSION['oups']['Auth-Mode'] = '';
        $_SESSION['oups']['week'] = '';

        $this->login($admin);

        $this->client->request('GET', '/agent');
        $this->client->waitFor('#tableAgents_wrapper');

        // DataTable initialized
        $this->assertTrue($this->client->executeScript("return DataTable.isDataTable('#tableAgents');"), 'CJDataTable initialized');

        // 25 rows by default (no data-length)
        $this->assertSame(25, $this->client->executeScript("return new DataTable.Api('#tableAgents').page.len();"), 'pageLength 25');
        $this->assertSame(25, $this->client->executeScript("return document.querySelectorAll('#tableAgents tbody tr').length;"), '25 rows displayed');

        // dataTableNoSort column not orderable, clear-string column type
        $this->assertFalse($this->client->executeScript("return new DataTable.Api('#tableAgents').column(0).orderable();"), 'dataTableNoSort column not orderable');
        $this->assertSame('clear-string', $this->client->executeScript("return new DataTable.Api('#tableAgents').column(1).type();"), 'clear-string type');

        // State saved (stateSave) in the local storage
        $this->client->executeScript("new DataTable.Api('#tableAgents').order([2, 'desc']).draw();");
        $this->assertTrue(
            $this->client->executeScript("return Object.keys(localStorage).some(function (k) { return k.indexOf('DataTables_tableAgents') === 0; });"),
            'state saved in localStorage'
        );

        // Export buttons (no data-noExport)
        $this->assertGreaterThan(0, $this->client->executeScript("return document.querySelectorAll('#tableAgents_wrapper .dt-buttons').length;"), 'export buttons');
    }
}
