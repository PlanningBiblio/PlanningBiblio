<?php

use App\Entity\Agent;
use Tests\PLBWebTestCase;

/**
 * Automatic initialization of the CJDataTable tables (public/js/datatables-init.js), in a browser.
 * Table used: list of agents (/agent), with a dataTableNoSort column.
 */
class DataTablesInitTest extends PLBWebTestCase
{
    public function testCJDataTable(): void
    {
        $this->builder->delete(Agent::class);

        $admin = $this->builder->build(Agent::class, [
            'login' => 'dtadmin', 'nom' => 'Admin', 'prenom' => 'Data',
            'arrivee' => new \DateTime('2020-01-01'),
            'droits' => [21, 4, 99, 100], 'supprime' => 0, 'actif' => 'Actif',
        ]);

        // Arrival dates of the first agents: sorted on data-order (Y-m-d), not on the displayed text (d/m/Y).
        $arrivals = [1 => '2026-01-15', 2 => '2025-12-01', 3 => '2026-02-03'];

        for ($i = 1; $i <= 30; $i++) {
            $this->builder->build(Agent::class, [
                'login' => 'dtagent' . $i, 'nom' => sprintf('Agent%02d', $i), 'prenom' => 'Test',
                'supprime' => 0, 'actif' => 'Actif',
                // The other agents get an older arrival date: without value, FixtureBuilder sets today's date
                'arrivee' => new \DateTime($arrivals[$i] ?? '2020-01-01'),
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

        // Export buttons (no data-no-export)
        $this->assertGreaterThan(0, $this->client->executeScript("return document.querySelectorAll('#tableAgents_wrapper .dt-buttons').length;"), 'export buttons');

        // French language file: length menu label for all the rows
        $this->client->waitFor('#tableAgents_wrapper select option[value="-1"]');
        $this->assertSame('Tout', trim($this->client->executeScript("return document.querySelector('#tableAgents_wrapper select option[value=\"-1\"]').textContent;")), 'language file');

        // Order data (data-order): arrival dates sorted chronologically, descending -> 03/02/2026 first
        // (a sort on the displayed text would give 15/01/2026 first)
        // (column found by its header: the Sites column only exists in multi-sites configuration)
        $first = $this->client->executeScript(
            "var api = new DataTable.Api('#tableAgents');"
            . " var col = api.columns().header().toArray().findIndex(function (th) { return th.textContent.trim() === 'Arrivée'; });"
            . " api.order([col, 'desc']).draw();"
            . " return api.cell(api.rows({ order: 'applied' }).indexes()[0], col).data();"
        );
        $this->assertSame('03/02/2026', trim(strip_tags((string) $first)), 'data-order on the dates');
    }

    public function testBooleanAttributes(): void
    {
        $this->setUpPantherClient();
        $this->client->request('GET', '/login');

        $this->assertFalse($this->client->executeScript("return CJDataTableBoolean('false', true);"));
        $this->assertFalse($this->client->executeScript("return CJDataTableBoolean('0', true);"));
        $this->assertTrue($this->client->executeScript("return CJDataTableBoolean('1', false);"));
        $this->assertTrue($this->client->executeScript("return CJDataTableBoolean(undefined, true);"));
    }
}
