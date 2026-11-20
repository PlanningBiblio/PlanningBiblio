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

        // Order data (data-order): the arrival dates must be sorted chronologically.
        // Only the relative positions of 3 agents of the test are checked, so that the other rows
        // (agents of the installation, agents without arrival date) have no influence:
        // descending, chronological order: Agent03 (03/02/2026), Agent01 (15/01/2026), Agent02 (01/12/2025);
        // a sort on the displayed text (d/m/Y) would give Agent01 (15/...), Agent03 (03/...), Agent02 (01/...).
        // The rows are returned with their values, to make a failure easier to understand.
        // The rows are read in the DOM (all the rows displayed: page length -1), not with the rows() API
        $result = $this->client->executeScript(
            "var api = new DataTable.Api('#tableAgents');"
            . " var headers = Array.prototype.map.call(document.querySelectorAll('#tableAgents thead th'), function (th) { return th.textContent.trim(); });"
            . " var col = headers.indexOf('Arrivée');"
            . " var nameCol = headers.indexOf('Nom');"
            . " var before = { apiRows: api.rows().count(), domRows: document.querySelectorAll('#tableAgents tbody tr').length, page: api.page.info() };"
            . " api.search('').order([col, 'desc']).page.len(-1).draw();"
            . " var rows = Array.prototype.map.call(document.querySelectorAll('#tableAgents tbody tr'), function (tr) {"
            . "   var name = tr.cells[nameCol], date = tr.cells[col];"
            . "   return [name ? name.textContent.trim() : null, date ? date.textContent.trim() : null, date ? date.getAttribute('data-order') : null];"
            . " });"
            . " return { headers: headers, col: col, nameCol: nameCol, type: col >= 0 ? api.column(col).type() : null, before: before, rows: rows };"
        );

        $names = array_column($result['rows'], 0);
        $debug = json_encode([
            'headers' => $result['headers'],
            'before' => $result['before'],
            'type' => $result['type'],
            'rows' => array_slice($result['rows'], 0, 12),
            'total' => count($result['rows']),
        ], JSON_UNESCAPED_UNICODE);

        $positions = [];
        foreach (['Agent03', 'Agent01', 'Agent02'] as $name) {
            $position = array_search($name, $names, true);
            $this->assertNotFalse($position, "$name in the table: $debug");
            $positions[] = $position;
        }

        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, "data-order on the dates (Agent03, Agent01, Agent02): $debug");
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
