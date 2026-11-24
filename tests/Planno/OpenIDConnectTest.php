<?php

use App\Planno\OpenIDConnect;
use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Unit tests for App\Planno\OpenIDConnect.
 *
 * No database and no network: the OpenID Connect client is mocked and the
 * entity manager is replaced by a fake that captures log messages.
 */
class OpenIDConnectTest extends TestCase
{
    private $savedConfig;
    private $savedEntityManager;
    private $fakeEntityManager;

    protected function setUp(): void
    {
        $this->savedConfig = $GLOBALS['config'] ?? null;
        $this->savedEntityManager = $GLOBALS['entityManager'] ?? null;

        // Captures messages written by LoggerTrait::log()
        $this->fakeEntityManager = new class {
            public array $logs = [];
            public function persist($log): void { $this->logs[] = $log->getMessage(); }
            public function flush(): void {}
        };
        $GLOBALS['entityManager'] = $this->fakeEntityManager;
    }

    protected function tearDown(): void
    {
        $GLOBALS['config'] = $this->savedConfig;
        $GLOBALS['entityManager'] = $this->savedEntityManager;
    }

    // ------------------------------------------------------------------
    // Claims merging (OpenID Connect Core 1.0 §5.3.2 and §5.4)
    // ------------------------------------------------------------------

    public function testMergeClaimsUserInfoTakesPrecedence(): void
    {
        $merged = OpenIDConnect::mergeClaims(
            ['sub' => 'subject-1', 'email' => 'old@example.org'],
            ['sub' => 'subject-1', 'email' => 'new@example.org']
        );

        $this->assertSame('new@example.org', $merged['email']);
    }

    public function testMergeClaimsKeepsIdTokenOnlyClaims(): void
    {
        $merged = OpenIDConnect::mergeClaims(
            ['sub' => 'subject-1', 'preferred_username' => 'jdoe@example.onmicrosoft.com', 'oid' => 'object-id-1'],
            ['sub' => 'subject-1', 'email' => 'jane.doe@example.org']
        );

        $this->assertSame('jdoe@example.onmicrosoft.com', $merged['preferred_username']);
        $this->assertSame('object-id-1', $merged['oid']);
        $this->assertSame('jane.doe@example.org', $merged['email']);
    }

    public function testMergeClaimsRejectsSubMismatch(): void
    {
        $merged = OpenIDConnect::mergeClaims(
            ['sub' => 'subject-1'],
            ['sub' => 'subject-2', 'email' => 'attacker@example.org']
        );

        $this->assertNull($merged, 'UserInfo with another sub must not be used');
    }

    public function testMergeClaimsAcceptsUserInfoWithoutSub(): void
    {
        $merged = OpenIDConnect::mergeClaims(['sub' => 'subject-1'], ['email' => 'jane.doe@example.org']);

        $this->assertSame('subject-1', $merged['sub']);
        $this->assertSame('jane.doe@example.org', $merged['email']);
    }

    public function testMergeClaimsWithEmptyUserInfo(): void
    {
        $merged = OpenIDConnect::mergeClaims(['sub' => 'subject-1', 'email' => 'jane.doe@example.org'], []);

        $this->assertSame('jane.doe@example.org', $merged['email']);
    }

    // ------------------------------------------------------------------
    // User building
    // ------------------------------------------------------------------

    public function testBuildUserDefaultsToEmailAttribute(): void
    {
        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => '']);

        $user = $oidc->buildUser([
            'given_name' => 'Jane',
            'family_name' => 'Doe',
            'email' => 'jane.doe@example.org',
        ]);

        $this->assertSame('Jane', $user->firstname);
        $this->assertSame('Doe', $user->lastname);
        $this->assertSame('jane.doe@example.org', $user->email);
        $this->assertSame('jane.doe@example.org', $user->login);
    }

    public function testBuildUserUsesConfiguredLoginAttribute(): void
    {
        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'preferred_username']);

        $user = $oidc->buildUser([
            'preferred_username' => 'jdoe@example.onmicrosoft.com',
            'email' => 'jane.doe@example.org',
        ]);

        $this->assertSame('jdoe@example.onmicrosoft.com', $user->login);
        $this->assertSame('jane.doe@example.org', $user->email);
    }

    public function testBuildUserIgnoresNonScalarClaims(): void
    {
        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'groups']);

        $user = $oidc->buildUser(['groups' => ['group-1', 'group-2']]);

        $this->assertNull($user->login);
    }

    public function testBuildUserWithMissingClaims(): void
    {
        $oidc = $this->createOpenIDConnect();

        $user = $oidc->buildUser(['sub' => 'subject-1']);

        $this->assertNull($user->firstname);
        $this->assertNull($user->lastname);
        $this->assertNull($user->email);
        $this->assertNull($user->login);
    }

    // ------------------------------------------------------------------
    // Redirect URI
    // ------------------------------------------------------------------

    /**
     * @dataProvider redirectUrlProvider
     */
    public function testGetRedirectUrl(string $plannoUrl, ?string $expected): void
    {
        $oidc = $this->createOpenIDConnect(['URL' => $plannoUrl]);

        $this->assertSame($expected, $oidc->getRedirectUrl());
    }

    public function redirectUrlProvider(): array
    {
        return [
            'local docker port' => ['http://localhost:8080', 'http://localhost:8080/login'],
            'trailing slash' => ['https://planning.example.org/', 'https://planning.example.org/login'],
            'sub directory' => ['https://www.example.org/planno', 'https://www.example.org/planno/login'],
            'not configured' => ['', null],
        ];
    }

    // ------------------------------------------------------------------
    // Authentication flow
    // ------------------------------------------------------------------

    public function testAuthConfiguresClientWithoutGuessingRedirectUrl(): void
    {
        $client = $this->createClientMock(['sub' => 'subject-1', 'email' => 'jane.doe@example.org'], ['sub' => 'subject-1']);
        $client->expects($this->once())->method('setHttpUpgradeInsecureRequests')->with(false);
        $client->expects($this->once())->method('setRedirectURL')->with('http://localhost:8080/login');
        $client->expects($this->once())->method('addScope')->with(['openid', 'email', 'profile']);
        $client->expects($this->never())->method('setCertPath');

        $oidc = $this->createOpenIDConnect(['URL' => 'http://localhost:8080'], $client);

        $this->assertNotFalse($oidc->auth($this->createRequest()));
    }

    public function testAuthSetsCertPathWhenConfigured(): void
    {
        $client = $this->createClientMock(['sub' => 'subject-1'], []);
        $client->expects($this->once())->method('setCertPath')->with('/etc/ssl/certs/provider-ca.pem');

        $oidc = $this->createOpenIDConnect(['OIDC-CACert' => '/etc/ssl/certs/provider-ca.pem'], $client);
        $oidc->auth($this->createRequest());
    }

    public function testAuthWithMicrosoftEntraIdPreferredUsername(): void
    {
        // Entra ID: preferred_username is only in the ID Token, UserInfo cannot be customized
        $client = $this->createClientMock(
            ['sub' => 'subject-1', 'oid' => 'object-id-1', 'preferred_username' => 'jdoe@example.onmicrosoft.com'],
            ['sub' => 'subject-1', 'given_name' => 'Jane', 'family_name' => 'Doe', 'email' => 'jane.doe@example.org']
        );
        $client->expects($this->once())->method('requestUserInfo');

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('set')->with('oidcToken', 'raw-id-token');

        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'preferred_username'], $client);
        $user = $oidc->auth($this->createRequest($session));

        $this->assertSame('jdoe@example.onmicrosoft.com', $user->login);
        $this->assertSame('jane.doe@example.org', $user->email);
        $this->assertSame('Jane', $user->firstname);
        $this->assertSame('Doe', $user->lastname);
    }

    public function testAuthWithMicrosoftEntraIdEmail(): void
    {
        // Entra ID with mail filled in: email comes from UserInfo
        $client = $this->createClientMock(
            ['sub' => 'subject-1', 'preferred_username' => 'jdoe@example.onmicrosoft.com'],
            ['sub' => 'subject-1', 'email' => 'jane.doe@example.org']
        );

        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'email'], $client);
        $user = $oidc->auth($this->createRequest());

        $this->assertSame('jane.doe@example.org', $user->login);
    }

    public function testAuthWithClaimsInUserInfoOnly(): void
    {
        // LemonLDAP::NG default: minimal ID Token, claims released by UserInfo
        $client = $this->createClientMock(
            ['sub' => 'jdoe'],
            ['sub' => 'jdoe', 'email' => 'jane.doe@example.org', 'given_name' => 'Jane', 'family_name' => 'Doe']
        );

        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'email'], $client);
        $user = $oidc->auth($this->createRequest());

        $this->assertSame('jane.doe@example.org', $user->login);
        $this->assertSame('Jane', $user->firstname);
    }

    public function testAuthWithClaimsInIdTokenOnly(): void
    {
        // Provider configured to release all claims in the ID Token
        $client = $this->createClientMock(['sub' => 'jdoe', 'email' => 'jane.doe@example.org'], null);

        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'email'], $client);
        $user = $oidc->auth($this->createRequest());

        $this->assertSame('jane.doe@example.org', $user->login);
    }

    public function testAuthFallsBackToIdTokenWhenUserInfoFails(): void
    {
        $client = $this->createClientMock(
            ['sub' => 'subject-1', 'email' => 'jane.doe@example.org'],
            new OpenIDConnectClientException('The communication to retrieve user data has failed with status code 500')
        );

        $oidc = $this->createOpenIDConnect([], $client);
        $user = $oidc->auth($this->createRequest());

        $this->assertSame('jane.doe@example.org', $user->login);
        $this->assertLogContains('UserInfo unavailable');
    }

    public function testAuthRefusesUserInfoWithAnotherSub(): void
    {
        $client = $this->createClientMock(
            ['sub' => 'subject-1'],
            ['sub' => 'subject-2', 'email' => 'attacker@example.org']
        );

        $oidc = $this->createOpenIDConnect([], $client);

        $this->assertFalse($oidc->auth($this->createRequest()));
        $this->assertLogContains('UserInfo sub does not match ID Token sub');
    }

    public function testAuthReturnsFalseOnProviderError(): void
    {
        // Previously "catch (Exception $e)" resolved to App\Planno\Exception: HTTP 500
        $client = $this->createMock(OpenIDConnectClient::class);
        $client->method('authenticate')
            ->willThrowException(new OpenIDConnectClientException('Error: access_denied Description: The user has cancelled the sign-in'));

        $oidc = $this->createOpenIDConnect([], $client);

        $this->assertFalse($oidc->auth($this->createRequest()));
        $this->assertLogContains('Authentication failed: Error: access_denied');
    }

    public function testDebugLogContainsClaimNamesOnly(): void
    {
        $client = $this->createClientMock(
            ['sub' => 'subject-1'],
            ['sub' => 'subject-1', 'email' => 'jane.doe@example.org', 'name' => 'Jane Doe']
        );

        $oidc = $this->createOpenIDConnect(['OIDC-Debug' => 1, 'OIDC-LoginAttribute' => 'sub'], $client);
        $oidc->auth($this->createRequest());

        $this->assertCount(1, $this->fakeEntityManager->logs);
        $log = json_decode($this->fakeEntityManager->logs[0], true);
        $this->assertSame(['sub', 'email', 'name'], $log['claims']);
        $this->assertSame('sub', $log['login_attribute']);
        $this->assertStringNotContainsString('jane.doe@example.org', $this->fakeEntityManager->logs[0]);
        $this->assertStringNotContainsString('Jane Doe', $this->fakeEntityManager->logs[0]);
    }

    public function testNoLogWhenDebugIsDisabled(): void
    {
        $client = $this->createClientMock(['sub' => 'subject-1', 'email' => 'jane.doe@example.org'], []);

        $oidc = $this->createOpenIDConnect(['OIDC-Debug' => 0], $client);
        $oidc->auth($this->createRequest());

        $this->assertSame([], $this->fakeEntityManager->logs);
    }

    // ------------------------------------------------------------------
    // Missing login attribute and Microsoft v1.0 endpoint detection
    // ------------------------------------------------------------------

    /**
     * @dataProvider microsoftV1Provider
     */
    public function testIsMicrosoftV1(array $claims, bool $expected): void
    {
        $this->assertSame($expected, OpenIDConnect::isMicrosoftV1($claims));
    }

    public function microsoftV1Provider(): array
    {
        return [
            'v1.0 ID Token' => [self::microsoftV1Claims(), true],
            'v1.0 issuer without ver' => [['iss' => 'https://sts.windows.net/00000000-0000-0000-0000-000000000000/'], true],
            'v2.0 ID Token' => [[
                'iss' => 'https://login.microsoftonline.com/00000000-0000-0000-0000-000000000000/v2.0',
                'ver' => '2.0',
                'preferred_username' => 'jdoe@example.onmicrosoft.com',
            ], false],
            'other provider' => [['iss' => 'https://auth.example.org', 'sub' => 'jdoe'], false],
            'no claims' => [[], false],
        ];
    }

    public function testAuthWarnsAboutMicrosoftV1Endpoint(): void
    {
        // Claims observed with OIDC-Provider = https://login.microsoftonline.com/<tenant> (no /v2.0)
        $client = $this->createClientMock(self::microsoftV1Claims(), new OpenIDConnectClientException('The communication to retrieve user data has failed with status code 401'));

        $oidc = $this->createOpenIDConnect([
            'OIDC-Provider' => 'https://login.microsoftonline.com/00000000-0000-0000-0000-000000000000',
            'OIDC-LoginAttribute' => 'email',
        ], $client);
        $user = $oidc->auth($this->createRequest());

        $this->assertNull($user->login);
        $this->assertLogContains("Login attribute 'email' not found in claims: aud, iss, iat");
        $this->assertLogContains('Microsoft Entra ID v1.0 endpoint detected');
        $this->assertLogContains('/v2.0');
    }

    public function testAuthLogsMissingLoginAttributeWithoutV1Hint(): void
    {
        $client = $this->createClientMock(['iss' => 'https://auth.example.org', 'sub' => 'jdoe'], ['sub' => 'jdoe', 'name' => 'Jane Doe']);

        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'email'], $client);
        $oidc->auth($this->createRequest());

        $this->assertSame(["Login attribute 'email' not found in claims: iss, sub, name"], $this->fakeEntityManager->logs);
    }

    public function testNoWarningWhenLoginIsResolvedOnMicrosoftV1(): void
    {
        // v1.0 still works with an attribute it provides, e.g. upn
        $client = $this->createClientMock(self::microsoftV1Claims(), []);

        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'upn'], $client);
        $user = $oidc->auth($this->createRequest());

        $this->assertSame('jdoe@example.onmicrosoft.com', $user->login);
        $this->assertSame([], $this->fakeEntityManager->logs);
    }

    public function testMissingLoginMessageContainsNoClaimValues(): void
    {
        $oidc = $this->createOpenIDConnect(['OIDC-LoginAttribute' => 'email']);

        $message = $oidc->missingLoginMessage(self::microsoftV1Claims());

        $this->assertStringNotContainsString('jdoe@example.onmicrosoft.com', $message);
        $this->assertStringNotContainsString('Jane Doe', $message);
        $this->assertStringNotContainsString('203.0.113.10', $message);
    }

    // ------------------------------------------------------------------
    // Logout
    // ------------------------------------------------------------------

    public function testLogoutSendsPostLogoutRedirectUri(): void
    {
        $client = $this->createMock(OpenIDConnectClient::class);
        $client->expects($this->once())->method('signOut')->with('raw-id-token', 'http://localhost:8080/logout');

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('oidcToken')->willReturn('raw-id-token');

        $oidc = $this->createOpenIDConnect(['URL' => 'http://localhost:8080/'], $client);
        $oidc->logout($this->createRequest($session));
    }

    public function testLogoutReturnsFalseOnProviderError(): void
    {
        $client = $this->createMock(OpenIDConnectClient::class);
        $client->method('signOut')->willThrowException(new OpenIDConnectClientException('The provider end_session_endpoint could not be fetched'));

        $oidc = $this->createOpenIDConnect([], $client);

        $this->assertFalse($oidc->logout($this->createRequest()));
        $this->assertLogContains('Logout failed');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Returns an OpenIDConnect instance whose client is replaced by $client.
     */
    private function createOpenIDConnect(array $config = [], ?OpenIDConnectClient $client = null): OpenIDConnect
    {
        $GLOBALS['config'] = array_merge([
            'URL' => 'http://localhost:8080',
            'OIDC-Provider' => 'https://login.microsoftonline.com/00000000-0000-0000-0000-000000000000/v2.0',
            'OIDC-CACert' => '',
            'OIDC-ClientID' => 'test-client-id',
            'OIDC-ClientSecret' => 'test-client-secret',
            'OIDC-LoginAttribute' => 'email',
            'OIDC-Debug' => 0,
        ], $config);

        $oidc = new class extends OpenIDConnect {
            public ?OpenIDConnectClient $client = null;

            protected function createClient(): OpenIDConnectClient
            {
                $this->configureClient($this->client);
                return $this->client;
            }
        };
        $oidc->client = $client ?? $this->createMock(OpenIDConnectClient::class);

        return $oidc;
    }

    /**
     * Client mock returning the given ID Token claims and UserInfo claims.
     *
     * @param array|null|\Throwable $userInfo claims, null for a response that
     *                                        cannot be decoded (e.g. signed JWT),
     *                                        or an exception thrown by the endpoint
     */
    private function createClientMock(array $idTokenClaims, $userInfo)
    {
        $client = $this->createMock(OpenIDConnectClient::class);
        $client->method('getIdToken')->willReturn('raw-id-token');
        $client->method('getVerifiedClaims')->willReturn((object) $idTokenClaims);

        if ($userInfo instanceof \Throwable) {
            $client->method('requestUserInfo')->willThrowException($userInfo);
        } else {
            $client->method('requestUserInfo')->willReturn($userInfo === null ? null : (object) $userInfo);
        }

        return $client;
    }

    /**
     * ID Token claims issued by the Microsoft Entra ID v1.0 endpoint
     * (same claim names as observed in a real test tenant, fake values).
     */
    private static function microsoftV1Claims(): array
    {
        return [
            'aud' => 'test-client-id',
            'iss' => 'https://sts.windows.net/00000000-0000-0000-0000-000000000000/',
            'iat' => 1764000000,
            'nbf' => 1764000000,
            'exp' => 1764003600,
            'amr' => ['pwd'],
            'ipaddr' => '203.0.113.10',
            'name' => 'Jane Doe',
            'nonce' => 'test-nonce',
            'oid' => '11111111-aaaa-4bbb-8ccc-000000000001',
            'rh' => 'test-rh',
            'sid' => 'test-session-id',
            'sub' => 'subject-1',
            'tid' => '00000000-0000-0000-0000-000000000000',
            'unique_name' => 'jdoe@example.onmicrosoft.com',
            'upn' => 'jdoe@example.onmicrosoft.com',
            'uti' => 'test-uti',
            'ver' => '1.0',
        ];
    }

    private function createRequest(?SessionInterface $session = null): Request
    {
        $request = $this->createMock(Request::class);
        $request->method('getSession')->willReturn($session ?? $this->createMock(SessionInterface::class));

        return $request;
    }

    private function assertLogContains(string $needle): void
    {
        foreach ($this->fakeEntityManager->logs as $log) {
            if (str_contains($log, $needle)) {
                $this->addToAssertionCount(1);
                return;
            }
        }
        $this->fail("No log message contains '$needle'. Logs: " . json_encode($this->fakeEntityManager->logs));
    }
}
