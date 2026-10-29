<?php

use App\Entity\Agent;
use App\Entity\Absence;
use App\Entity\AbsenceDocument;
use App\Entity\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AbsenceDocumentControllerTest extends PLBWebTestCase
{
    private array $tempFiles = [];

    public static function setUpBeforeClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);
        $builder->delete(AbsenceDocument::class);

    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);
        $builder->delete(AbsenceDocument::class);
    }

    protected function tearDown(): void
    {
        // Clean up temporary files created during tests
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];

        parent::tearDown();
    }

    public function testDelete(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $jdoe = $builder->build(Agent::class, [
            'login' => 'jdoe',
        ]);

        $absence = $builder->build(Absence::class, ['perso_id' => $jdoe->getId(), 'groupe' => '']);
        // Use an empty filename so that deleteFile does not try to unlink a non-existing file
        $absenceDocument = $builder->build(AbsenceDocument::class, ['absence_id' => $absence->getId(), 'filename' => '']);

        $this->logInAgent($jdoe, [6, 99, 100]);

        $crawler = $this->client->request('GET', '/absence/' . $absence->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, '#documentsList');
        $this->assertSelectorCount(1, '#documentsList > div');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion fails without CSRF token
        $crawler = $this->client->request(
            'POST',
            '/absences/document/' . $absenceDocument->getId() . '/delete',
            []
        );
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->request('GET', '/absence/' . $absence->getId());
        $this->assertSelectorCount(1, '#documentsList > div');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion succeeds with CSRF token
        $crawler = $this->client->request(
            'POST',
            '/absences/document/' . $absenceDocument->getId() . '/delete',
            ['_token' => $token]
        );
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', '/absence/' . $absence->getId());
        $this->assertSelectorCount(0, '#documentsList > div');
    }

    /**
     * Success case: POST with valid CSRF token and valid file should return 200 OK
     * and create an AbsenceDocument in the database
     */
    public function testAddSuccess(): void
    {
        $jdoe = $this->createAgentWithAbsence();
        $this->setDocumentConfig('text/plain', 'txt');

        $token = $this->getCsrfToken($jdoe['absence']->getId());

        // Create a valid text file
        $fileContent = 'This is a test document content.';
        $file = $this->createTempFile('test_document.txt', $fileContent);

        $this->client->request(
            'POST',
            '/absences/document/' . $jdoe['absence']->getId(),
            ['_token' => $token],
            ['documentFile' => $this->createUploadedFile($file, 'test_document.txt', 'text/plain')]
        );

        // Assert successful response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($this->client->getResponse()->getContent(), true);

        // Assert response structure
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('absence_id', $response);
        $this->assertArrayHasKey('filename', $response);

        // Assert response values
        $this->assertSame($jdoe['absence']->getId(), $response['absence_id']);
        $this->assertSame('test_document.txt', $response['filename']);
        $this->assertNotEmpty($response['id']);
        $this->assertIsInt($response['id']);

        // Assert the AbsenceDocument was created in the database
        $absenceDocument = $this->entityManager->getRepository(AbsenceDocument::class)
            ->find($response['id']);
        $this->assertNotNull($absenceDocument, 'AbsenceDocument should be persisted in database');
        $this->assertSame($jdoe['absence']->getId(), $absenceDocument->getAbsenceId());
        $this->assertSame('test_document.txt', $absenceDocument->getFilename());
        $this->assertNotNull($absenceDocument->getStorageId());
        $this->assertNotNull($absenceDocument->getDate());

        // Assert the file was saved to the storage directory
        $storagePath = $absenceDocument->getStoragePath();
        $this->assertFileExists($storagePath, 'Uploaded file should exist in storage directory');

        // Assert the file content matches
        $this->assertSame($fileContent, file_get_contents($storagePath));

        // Clean up: delete the uploaded file and the AbsenceDocument
        $storageDir = $absenceDocument->getStorageDirectory();
        $absenceDocument->deleteFile();

        // Remove the absence directory if empty
        $absenceDir = dirname($storageDir);
        if (is_dir($absenceDir) && count(scandir($absenceDir)) === 2) {
            rmdir($absenceDir);
        }

        $this->entityManager->remove($absenceDocument);
        $this->entityManager->flush();
    }

    /**
     * Error case: POST without CSRF token should return 403 Forbidden
     */
    public function testAddFailsWithoutCsrfToken(): void
    {
        $jdoe = $this->createAgentWithAbsence();
        $this->setDocumentConfig('text/plain', 'txt');

        $file = $this->createTempFile('test.txt', 'plain text content');

        $this->client->request(
            'POST',
            '/absences/document/' . $jdoe['absence']->getId(),
            [],
            ['documentFile' => $this->createUploadedFile($file, 'test.txt', 'text/plain')]
        );

        $this->assertResponseStatusCodeSame(403);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertContains('CSRF token validation failed', $response['errors']);
    }

    /**
     * Error case: POST with invalid CSRF token should return 403 Forbidden
     */
    public function testAddFailsWithInvalidCsrfToken(): void
    {
        $jdoe = $this->createAgentWithAbsence();
        $this->setDocumentConfig('text/plain', 'txt');

        $file = $this->createTempFile('test.txt', 'plain text content');

        $this->client->request(
            'POST',
            '/absences/document/' . $jdoe['absence']->getId(),
            ['_token' => 'invalid_token'],
            ['documentFile' => $this->createUploadedFile($file, 'test.txt', 'text/plain')]
        );

        $this->assertResponseStatusCodeSame(403);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertContains('CSRF token validation failed', $response['errors']);
    }

    /**
     * Error case: POST with a file having an invalid extension should return 422 Unprocessable Entity
     */
    public function testAddFailsWithInvalidFileExtension(): void
    {
        $jdoe = $this->createAgentWithAbsence();
        // Allow only pdf extension and application/pdf mime type
        $this->setDocumentConfig('application/pdf', 'pdf');

        $token = $this->getCsrfToken($jdoe['absence']->getId());

        // Create a file with .exe extension (not allowed)
        $file = $this->createTempFile('test.exe', 'plain text content');

        $this->client->request(
            'POST',
            '/absences/document/' . $jdoe['absence']->getId(),
            ['_token' => $token],
            ['documentFile' => $this->createUploadedFile($file, 'test.exe', 'application/octet-stream')]
        );

        $this->assertResponseStatusCodeSame(422);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertNotEmpty($response['errors']);
        $this->assertSame('L\'extension du fichier est invalide ("exe"). Les extensions autorisées sont "pdf".', $response['errors'][0]);
        $this->assertSame('Le type du fichier est invalide ("text/plain"). Les types autorisés sont "application/pdf".', $response['errors'][1]);
    }

    /**
     * Error case: POST with a file having an invalid MIME type should return 422 Unprocessable Entity
     */
    public function testAddFailsWithInvalidMimeType(): void
    {
        $jdoe = $this->createAgentWithAbsence();
        // Allow only pdf extension and application/pdf mime type
        $this->setDocumentConfig('application/pdf', 'pdf');

        $token = $this->getCsrfToken($jdoe['absence']->getId());

        // Create a plain text file with .pdf extension
        // The extension is valid (pdf) but the mime type will be text/plain (not application/pdf)
        $file = $this->createTempFile('test.pdf', 'plain text content, not a real pdf');

        $this->client->request(
            'POST',
            '/absences/document/' . $jdoe['absence']->getId(),
            ['_token' => $token],
            ['documentFile' => $this->createUploadedFile($file, 'test.pdf', 'application/pdf')]
        );

        $this->assertResponseStatusCodeSame(422);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertNotEmpty($response['errors']);
        $this->assertSame('Le type du fichier est invalide ("text/plain"). Les types autorisés sont "application/pdf".', $response['errors'][0]);
    }

    /**
     * Error case: POST without any file
     *
     * The controller doesn't handle this case gracefully - it tries to access $ad->getId()
     * on an undefined variable. However, in the test environment, the request may be blocked
     * by the authorization listener (403) before reaching the controller.
     */
    public function testAddFailsWithoutFile(): void
    {
        $jdoe = $this->createAgentWithAbsence();
        $this->setDocumentConfig('text/plain', 'txt');

        $token = $this->getCsrfToken($jdoe['absence']->getId());

        // POST without any file
        $this->client->request(
            'POST',
            '/absences/document/' . $jdoe['absence']->getId(),
            ['_token' => $token],
            []
        );

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertNotEmpty($response['errors']);
        $this->assertSame('No files were sent', $response['errors'][0]);
    }

    /**
     * Helper: Create an agent with an absence and log them in
     *
     * @return array{agent: Agent, absence: Absence}
     */
    private function createAgentWithAbsence(): array
    {
        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, [
            'login' => 'jdoe_' . uniqid(),
        ]);

        $absence = $builder->build(Absence::class, ['perso_id' => $agent->getId(), 'groupe' => '']);

        $this->logInAgent($agent, [6, 99, 100]);

        return ['agent' => $agent, 'absence' => $absence];
    }

    /**
     * Helper: Set the document validation config (allowed mime types and extensions)
     */
    private function setDocumentConfig(string $mimeTypes, string $extensions): void
    {
        $configRepo = $this->entityManager->getRepository(Config::class);
        $configRepo->setParam('Absences-documents-typesMime', $mimeTypes);
        $configRepo->setParam('Absences-documents-extensions', $extensions);
    }

    /**
     * Helper: Get a valid CSRF token from the absence edit page
     */
    private function getCsrfToken(int $absenceId): string
    {
        $crawler = $this->client->request('GET', '/absence/' . $absenceId);
        $this->assertResponseIsSuccessful();

        return $crawler->filter('#_token')->attr('value');
    }

    /**
     * Helper: Create a temporary file with the given name and content
     */
    private function createTempFile(string $filename, string $content): string
    {
        $path = sys_get_temp_dir() . '/' . uniqid('planno_test_') . '_' . $filename;
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * Helper: Create an UploadedFile instance for testing
     */
    private function createUploadedFile(string $path, string $originalName, string $mimeType): UploadedFile
    {
        return new UploadedFile($path, $originalName, $mimeType, null, true);
    }
}
