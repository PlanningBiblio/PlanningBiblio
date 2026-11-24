<?php

use App\Entity\Agent;
use App\Entity\Config;
use App\Planno\MSGraphClient;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Planno\MSGraphClient.
 *
 * No database and no network: Graph responses are served by an overridden
 * sendGet(), and the entity manager / DBAL connection are fakes that record
 * the SQL statements.
 */
class MSGraphClientTest extends TestCase
{
    private const USER_ID_JANE = '11111111-aaaa-4bbb-8ccc-000000000001';
    private const USER_ID_JOHN = '11111111-aaaa-4bbb-8ccc-000000000002';

    // ------------------------------------------------------------------
    // graphKey(): mail address => Graph object id
    // ------------------------------------------------------------------

    public function testGraphKeyResolvesMailToObjectId(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->usersResponse([['id' => self::USER_ID_JANE, 'userPrincipalName' => 'jdoe@example.onmicrosoft.com']]);
        });

        $this->assertSame(self::USER_ID_JANE, $client->graphKey('jane.doe@example.org'));
        $this->assertSame(
            ["/users?\$filter=mail%20eq%20%27jane.doe%40example.org%27&\$select=id,userPrincipalName"],
            $client->requests
        );
    }

    public function testGraphKeyIsCached(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->usersResponse([['id' => self::USER_ID_JANE, 'userPrincipalName' => 'jdoe@example.onmicrosoft.com']]);
        });

        $client->graphKey('jane.doe@example.org');
        $client->graphKey('jane.doe@example.org');

        $this->assertCount(1, $client->requests);
    }

    public function testGraphKeyAppliesLoginSuffix(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->usersResponse([['id' => self::USER_ID_JANE, 'userPrincipalName' => 'jdoe@example.onmicrosoft.com']]);
        }, ['MSGraph-LoginSuffix' => '@example.org']);

        $this->assertSame(self::USER_ID_JANE, $client->graphKey('jane.doe'));
        $this->assertStringContainsString('jane.doe%40example.org', $client->requests[0]);
    }

    public function testGraphKeyFallsBackToUpnWhenNoUserMatches(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->usersResponse([]);
        });

        $this->assertSame('john.smith%40example.org', $client->graphKey('john.smith@example.org'));
    }

    public function testGraphKeyFallsBackToUpnWhenSeveralUsersMatch(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->usersResponse([
                ['id' => self::USER_ID_JANE, 'userPrincipalName' => 'jdoe@example.onmicrosoft.com'],
                ['id' => self::USER_ID_JOHN, 'userPrincipalName' => 'shared-mailbox@example.onmicrosoft.com'],
            ]);
        });

        $this->assertSame('jane.doe%40example.org', $client->graphKey('jane.doe@example.org'));
        $this->assertLogContains($client, 'matches several users');
    }

    public function testGraphKeyDisablesLookupWhenPermissionIsMissing(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->response(403, ['error' => ['code' => 'Authorization_RequestDenied']]);
        });

        $this->assertSame('jane.doe%40example.org', $client->graphKey('jane.doe@example.org'));
        $this->assertSame('john.smith%40example.org', $client->graphKey('john.smith@example.org'));

        $this->assertCount(1, $client->requests, 'Lookup must not be retried after a refusal');
        $this->assertLogContains($client, 'User.Read.All');
    }

    public function testGraphKeySkipsLookupForLoginWithoutAtSign(): void
    {
        $client = $this->createClient(function (string $url) {
            $this->fail("Unexpected Graph request: $url");
        });

        $this->assertSame('jdoe', $client->graphKey('jdoe'));
        $this->assertSame([], $client->requests);
    }

    public function testGraphKeyEscapesQuotesInODataFilter(): void
    {
        $client = $this->createClient(function (string $url) {
            return $this->usersResponse([]);
        });

        $client->graphKey("sean.o'brien@example.org");

        // mail eq 'sean.o''brien@example.org'
        $this->assertStringContainsString('%27sean.o%27%27brien%40example.org%27', $client->requests[0]);
    }

    public function testGraphKeyHandlesNullLogin(): void
    {
        $client = $this->createClient(function (string $url) {
            $this->fail("Unexpected Graph request: $url");
        });

        $this->assertSame('', $client->graphKey(null));
    }

    // ------------------------------------------------------------------
    // Import: resolved id used in every request
    // ------------------------------------------------------------------

    public function testImportUsesResolvedObjectIdForAllRequests(): void
    {
        $event = $this->event('event-1', 'ical-uid-1');

        $client = $this->createClient(function (string $url) use ($event) {
            if (str_starts_with($url, '/users?')) {
                return $this->usersResponse([['id' => self::USER_ID_JANE, 'userPrincipalName' => 'jdoe@example.onmicrosoft.com']]);
            }
            if (str_contains($url, '/calendarView')) {
                return $this->response(200, ['value' => [$event]]);
            }
            return $this->response(200, ['id' => 'ok']);
        }, [], [$this->agent(1, 'jane.doe@example.org')]);

        $client->retrieveEvents();

        foreach ($client->requests as $url) {
            if (str_starts_with($url, '/users/')) {
                $this->assertStringStartsWith('/users/' . self::USER_ID_JANE . '/', $url);
            }
        }
        $inserts = $client->connection->executed('INSERT');
        $this->assertCount(1, $inserts);
        $this->assertSame(1, $inserts[0]['params']['perso_id']);
        $this->assertSame('ical-uid-1', $inserts[0]['params']['ical_key']);
        $this->assertSame('Office 365', $inserts[0]['params']['motif']);
    }

    // ------------------------------------------------------------------
    // Import: partial failures must not delete local absences
    // ------------------------------------------------------------------

    public function testFailedCalendarViewKeepsLocalAbsences(): void
    {
        $client = $this->createClient(function (string $url) {
            if (str_starts_with($url, '/users?')) {
                return $this->usersResponse([]);
            }
            if (str_contains($url, '/calendarView')) {
                return $this->response(503, ['error' => ['code' => 'ServiceNotAvailable']]);
            }
            return $this->response(200, ['id' => 'ok']);
        }, [], [$this->agent(1, 'jane.doe@example.org')], [
            ['perso_id' => 1, 'ical_key' => 'ical-uid-local', 'last_modified' => '2026-11-01T08:00:00Z'],
        ]);

        $client->retrieveEvents();

        $this->assertSame([], $client->connection->executed('DELETE'));
        $this->assertLogContains($client, 'user skipped, local absences kept');
    }

    public function testFailedPaginationKeepsLocalAbsences(): void
    {
        $firstPage = [
            'value' => [$this->event('event-1', 'ical-uid-1')],
            '@odata.nextLink' => 'https://graph.microsoft.com/v1.0/users/next-page',
        ];

        $client = $this->createClient(function (string $url, bool $absolute) use ($firstPage) {
            if (str_starts_with($url, '/users?')) {
                return $this->usersResponse([]);
            }
            if ($absolute) {
                return $this->response(500, ['error' => ['code' => 'InternalServerError']]);
            }
            if (str_contains($url, '/calendarView')) {
                return $this->response(200, $firstPage);
            }
            return $this->response(200, ['id' => 'ok']);
        }, [], [$this->agent(1, 'jane.doe@example.org')], [
            ['perso_id' => 1, 'ical_key' => 'ical-uid-local', 'last_modified' => '2026-11-01T08:00:00Z'],
        ]);

        $client->retrieveEvents();

        $this->assertSame([], $client->connection->executed('DELETE'));
        $this->assertSame([], $client->connection->executed('INSERT'), 'Partial pages must be discarded');
    }

    public function testFailureForOneUserDoesNotAffectOthers(): void
    {
        $client = $this->createClient(function (string $url) {
            if (str_starts_with($url, '/users?')) {
                return $this->usersResponse([]);
            }
            if (str_contains($url, '/calendarView')) {
                return str_contains($url, 'jane.doe')
                    ? $this->response(503, ['error' => ['code' => 'ServiceNotAvailable']])
                    : $this->response(200, ['value' => []]);
            }
            return $this->response(200, ['id' => 'ok']);
        }, [], [$this->agent(1, 'jane.doe@example.org'), $this->agent(2, 'john.smith@example.org')], [
            ['perso_id' => 1, 'ical_key' => 'ical-uid-jane', 'last_modified' => '2026-11-01T08:00:00Z'],
            ['perso_id' => 2, 'ical_key' => 'ical-uid-john', 'last_modified' => '2026-11-01T08:00:00Z'],
        ]);

        $client->retrieveEvents();

        $deletes = $client->connection->executed('DELETE');
        $this->assertCount(1, $deletes, 'Only the event removed from the reachable calendar is deleted');
        $this->assertSame(2, $deletes[0]['params']['perso_id']);
        $this->assertSame('ical-uid-john', $deletes[0]['params']['ical_key']);
    }

    public function testSuccessfulImportStillDeletesRemovedEvents(): void
    {
        $client = $this->createClient(function (string $url) {
            if (str_starts_with($url, '/users?')) {
                return $this->usersResponse([]);
            }
            if (str_contains($url, '/calendarView')) {
                return $this->response(200, ['value' => []]);
            }
            return $this->response(200, ['id' => 'ok']);
        }, [], [$this->agent(1, 'jane.doe@example.org')], [
            ['perso_id' => 1, 'ical_key' => 'ical-uid-removed', 'last_modified' => '2026-11-01T08:00:00Z'],
        ]);

        $client->retrieveEvents();

        $deletes = $client->connection->executed('DELETE');
        $this->assertCount(1, $deletes);
        $this->assertSame('ical-uid-removed', $deletes[0]['params']['ical_key']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Builds an MSGraphClient whose Graph calls are answered by $responder.
     *
     * @param callable $responder   function (string $url, bool $absolute): ?object
     * @param array    $config      MSGraph-* parameters overriding the defaults
     * @param array    $agents      agents returned by the Agent repository
     * @param array    $localEvents rows of the absences table
     */
    private function createClient(callable $responder, array $config = [], array $agents = [], array $localEvents = [])
    {
        $config = array_merge([
            'MSGraph-AbsenceReason' => 'Office 365',
            'MSGraph-LoginSuffix' => '',
            'MSGraph-IgnoredStatuses' => 'free;tentative',
        ], $config);

        $connection = new FakeGraphConnection($localEvents);
        $entityManager = new FakeGraphEntityManager($config, $agents, $connection);

        $client = new class($entityManager, 'test-tenant-id', 'test-client-id', 'test-client-secret', false, false, false) extends MSGraphClient {
            public $responder;
            public array $requests = [];
            public $connection;
            public $entityManager;

            public function __construct($entityManager, $tenantid, $clientid, $clientsecret, $full, $stdout, $user_id)
            {
                parent::__construct($entityManager, $tenantid, $clientid, $clientsecret, $full, $stdout, $user_id);
                $this->entityManager = $entityManager;
            }

            public function graphKey(?string $login): string
            {
                return parent::graphKey($login);
            }

            protected function sendGet($request, $absolute = false, $retry = 0)
            {
                $this->requests[] = $request;
                return ($this->responder)($request, $absolute);
            }
        };
        $client->responder = $responder;
        $client->connection = $connection;

        return $client;
    }

    private function response(int $code, array $body): object
    {
        return (object) ['code' => $code, 'body' => json_decode(json_encode($body))];
    }

    private function usersResponse(array $users): object
    {
        return $this->response(200, ['value' => $users]);
    }

    private function agent(int $id, string $login): object
    {
        return new class($id, $login) {
            public function __construct(private int $id, private string $login) {}
            public function getId(): int { return $this->id; }
            public function getLogin(): string { return $this->login; }
        };
    }

    /**
     * A busy event organized by the user, tomorrow from 9:00 to 17:00 UTC.
     */
    private function event(string $id, string $iCalUId): array
    {
        $day = date('Y-m-d', strtotime('+1 day'));

        return [
            'id' => $id,
            'iCalUId' => $iCalUId,
            'subject' => 'Training session',
            'sensitivity' => 'normal',
            'showAs' => 'busy',
            'type' => 'singleInstance',
            'isOrganizer' => true,
            'responseStatus' => ['response' => 'organizer'],
            'lastModifiedDateTime' => '2026-11-20T10:00:00Z',
            'start' => ['dateTime' => $day . 'T09:00:00.0000000', 'timeZone' => 'UTC'],
            'end' => ['dateTime' => $day . 'T17:00:00.0000000', 'timeZone' => 'UTC'],
        ];
    }

    private function assertLogContains($client, string $needle): void
    {
        foreach ($client->entityManager->logs as $log) {
            if (str_contains($log, $needle)) {
                $this->addToAssertionCount(1);
                return;
            }
        }
        $this->fail("No log message contains '$needle'. Logs: " . json_encode($client->entityManager->logs));
    }
}

/**
 * Fake entity manager: Config and Agent repositories, log capture, DBAL connection.
 */
class FakeGraphEntityManager
{
    public array $logs = [];

    public function __construct(private array $config, private array $agents, private FakeGraphConnection $connection) {}

    public function getRepository(string $class)
    {
        $entities = $class === Config::class
            ? array_map(fn ($name) => new FakeGraphConfig($name, $this->config[$name]), array_keys($this->config))
            : $this->agents;

        return new class($entities) {
            public function __construct(private array $entities) {}
            public function findBy(array $criteria): array { return $this->entities; }
        };
    }

    public function find(string $class, $id)
    {
        foreach ($this->agents as $agent) {
            if ($agent->getId() == $id) {
                return $agent;
            }
        }
        return null;
    }

    public function getConnection(): FakeGraphConnection
    {
        return $this->connection;
    }

    public function persist($log): void
    {
        $this->logs[] = $log->getMessage();
    }

    public function flush(): void {}
}

class FakeGraphConfig
{
    public function __construct(private string $name, private $value) {}
    public function getName(): string { return $this->name; }
    public function getValue() { return $this->value; }
}

/**
 * Fake DBAL connection recording executed statements.
 * SELECT statements return the local events of the users listed in "IN(...)".
 */
class FakeGraphConnection
{
    public array $statements = [];

    public function __construct(private array $localEvents) {}

    public function prepare(string $sql)
    {
        return new FakeGraphStatement($this, $sql);
    }

    public function record(string $sql, array $params): object
    {
        $this->statements[] = ['sql' => $sql, 'params' => $params];

        $rows = [];
        if (str_starts_with($sql, 'SELECT') && preg_match('/IN\(([\d,]+)\)/', $sql, $matches)) {
            $ids = array_map('intval', explode(',', $matches[1]));
            $rows = array_values(array_filter($this->localEvents, fn ($row) => in_array($row['perso_id'], $ids)));
        }

        return new class($rows) {
            public function __construct(private array $rows) {}
            public function fetchAllAssociative(): array { return $this->rows; }
        };
    }

    public function executed(string $verb): array
    {
        return array_values(array_filter($this->statements, fn ($s) => str_starts_with($s['sql'], $verb)));
    }
}

class FakeGraphStatement
{
    private array $bound = [];

    public function __construct(private FakeGraphConnection $connection, private string $sql) {}

    public function bindParam(string $name, &$value): void
    {
        $this->bound[ltrim($name, ':')] = &$value;
    }

    public function execute(?array $params = null): object
    {
        // Copy bound values at execution time (they are references)
        $values = $params ?? array_map(fn ($v) => $v, $this->bound);

        return $this->connection->record($this->sql, $values);
    }
}
