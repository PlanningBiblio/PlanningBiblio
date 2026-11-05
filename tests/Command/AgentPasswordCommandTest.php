<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\FixtureBuilder;

class AgentPasswordCommandTest extends KernelTestCase
{
    private const OLD_PASSWORD = 'Old-password1';

    private EntityManagerInterface $entityManager;
    private CommandTester $tester;
    private ?string $adminOriginalPassword = null;

    protected function setUp(): void
    {
        // Wide terminal: SymfonyStyle does not wrap the messages checked by the tests
        putenv('COLUMNS=500');

        $kernel = self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $config = $this->entityManager->getRepository(Config::class);
        $config->setParam('Auth-PasswordLength', 8);

        // FixtureBuilder::delete() keeps the agents 1 (admin) and 2 (everybody)
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        // The admin account is reused: its password is restored in tearDown()
        $admin = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'admin']);
        if (!$admin) {
            $admin = new Agent();
            $admin->setLogin('admin');
            $admin->setLastname('Administrateur');
            $this->entityManager->persist($admin);
        }
        $this->adminOriginalPassword = $admin->getPassword();
        $admin->setPassword(password_hash(self::OLD_PASSWORD, PASSWORD_BCRYPT));

        $agent = new Agent();
        $agent->setLogin('jdupont');
        $agent->setLastname('Dupont');
        $agent->setPassword(password_hash(self::OLD_PASSWORD, PASSWORD_BCRYPT));
        $this->entityManager->persist($agent);

        $this->entityManager->flush();

        $application = new Application($kernel);
        $this->tester = new CommandTester($application->find('app:agent:password'));
    }

    protected function tearDown(): void
    {
        $this->entityManager->clear();

        $admin = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'admin']);
        if ($admin) {
            $admin->setPassword($this->adminOriginalPassword);
            $this->entityManager->flush();
        }

        putenv('COLUMNS');

        parent::tearDown();
    }

    public function testInteractiveChangesAdminPassword(): void
    {
        $this->tester->setInputs(['New-password1', 'New-password1']);
        $this->tester->execute([]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('New-password1', $this->hashOf('admin')));
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('jdupont')), 'other agents are unchanged');
        $this->assertStringNotContainsString('New-password1', $this->tester->getDisplay(), 'the password is not displayed');
    }

    public function testInteractiveWithLoginOption(): void
    {
        $this->tester->setInputs(['New-password1', 'New-password1']);
        $this->tester->execute(['--login' => 'jdupont']);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('New-password1', $this->hashOf('jdupont')));
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testConfirmationMismatch(): void
    {
        $this->tester->setInputs(['New-password1', 'Other-password1']);
        $this->tester->execute([]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('do not match', $this->tester->getDisplay());
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testWeakPasswordIsRejected(): void
    {
        $this->tester->setInputs(['weak', 'weak']);
        $this->tester->execute([]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('too weak', $this->tester->getDisplay());
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testAllowWeakPassword(): void
    {
        $this->tester->setInputs(['weak', 'weak']);
        $this->tester->execute(['--allow-weak-password' => true]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('test instances only', $this->tester->getDisplay());
        $this->assertTrue(password_verify('weak', $this->hashOf('admin')));
    }

    public function testAllowWeakPasswordWithStdin(): void
    {
        $this->tester->setInputs(['admin']);
        $this->tester->execute(['--stdin' => true, '--allow-weak-password' => true], ['interactive' => false]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('admin', $this->hashOf('admin')));
    }

    public function testAllowWeakPasswordWithComplexPasswordHasNoWarning(): void
    {
        $this->tester->setInputs(['New-password1', 'New-password1']);
        $this->tester->execute(['--allow-weak-password' => true]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertStringNotContainsString('test instances only', $this->tester->getDisplay());
    }

    public function testEmptyPasswordIsRejectedEvenWithAllowWeakPassword(): void
    {
        $this->tester->setInputs(['', '']);
        $this->tester->execute(['--allow-weak-password' => true]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('cannot be empty', $this->tester->getDisplay());
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testMinimumLengthFromConfig(): void
    {
        $this->entityManager->getRepository(Config::class)->setParam('Auth-PasswordLength', 20);

        $this->tester->setInputs(['New-password1', 'New-password1']);
        $this->tester->execute([]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('at least 20 characters', $this->tester->getDisplay());
    }

    public function testMinLengthOptionOverridesConfigInteractive(): void
    {
        // Auth-PasswordLength = 8: 'Abc1!x' (6 characters) is accepted with --min-length=6
        $this->tester->setInputs(['Abc1!x', 'Abc1!x']);
        $this->tester->execute(['--min-length' => '6']);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('Abc1!x', $this->hashOf('admin')));
    }

    public function testMinLengthOptionIsStricterThanConfig(): void
    {
        $this->tester->setInputs(['New-password1', 'New-password1']);
        $this->tester->execute(['--min-length' => '20']);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('at least 20 characters', $this->tester->getDisplay());
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testMinLengthOptionNonInteractive(): void
    {
        $this->tester->setInputs(['Abc1!x']);
        $this->tester->execute(['--stdin' => true, '--min-length' => '6'], ['interactive' => false]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('Abc1!x', $this->hashOf('admin')));
    }

    public function testMinLengthOptionWithGenerate(): void
    {
        $password = $this->generate(['--min-length' => '24']);

        $this->assertSame(24, strlen($password));
        $this->assertTrue(password_verify($password, $this->hashOf('admin')));
    }

    public function testInvalidMinLengthOption(): void
    {
        foreach (['0', '-3', 'abc', '8.5', '73'] as $value) {
            $this->tester->execute(['--generate' => true, '--min-length' => $value], ['interactive' => false]);

            $this->assertSame(Command::INVALID, $this->tester->getStatusCode(), "--min-length=$value");
            $this->assertStringContainsString('integer between 1 and 72', $this->tester->getDisplay());
        }

        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testUnknownLogin(): void
    {
        $this->tester->execute(['--login' => 'unknown', '--generate' => true], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('No agent found', $this->tester->getDisplay());
    }

    public function testGenerate(): void
    {
        $password = $this->generate();

        $this->assertSame(16, strlen($password));
        $this->assertTrue(password_verify($password, $this->hashOf('admin')), 'the displayed password is the new one');
    }

    public function testGenerateWritesOnlyThePasswordOnStandardOutput(): void
    {
        $this->tester->execute(['--generate' => true], ['interactive' => false, 'capture_stderr_separately' => true]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertMatchesRegularExpression('/^\S{16}\n$/', $this->tester->getDisplay(), 'stdout only contains the password');
        $this->assertStringContainsString('has been changed', $this->tester->getErrorOutput(), 'messages are on stderr');
    }

    public function testAllowWeakPasswordStillChecksExplicitMinLength(): void
    {
        $this->tester->execute(['--password' => 'abc', '--allow-weak-password' => true, '--min-length' => '6'], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('at least 6 characters', $this->tester->getDisplay());

        $this->tester->execute(['--password' => 'abcdef', '--allow-weak-password' => true, '--min-length' => '6'], ['interactive' => false]);
        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('abcdef', $this->hashOf('admin')));
    }

    public function testOptionsAreCheckedBeforeTheAgent(): void
    {
        // Invalid options are reported even if the login does not exist
        $this->tester->execute(['--login' => 'unknown', '--min-length' => 'abc', '--generate' => true], ['interactive' => false]);

        $this->assertSame(Command::INVALID, $this->tester->getStatusCode());
    }

    public function testStdin(): void
    {
        $this->tester->setInputs(['Stdin-password1']);
        $this->tester->execute(['--stdin' => true], ['interactive' => false]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('Stdin-password1', $this->hashOf('admin')));
    }

    public function testNonInteractiveWithoutPassword(): void
    {
        $this->tester->execute([], ['interactive' => false]);

        $this->assertSame(Command::INVALID, $this->tester->getStatusCode());
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testGenerateAndStdinAreExclusive(): void
    {
        $this->tester->execute(['--generate' => true, '--stdin' => true], ['interactive' => false]);

        $this->assertSame(Command::INVALID, $this->tester->getStatusCode());
    }

    public function testPasswordOption(): void
    {
        $this->tester->execute(['--password' => 'Option-password1'], ['interactive' => false]);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('Option-password1', $this->hashOf('admin')));
        $this->assertStringContainsString('Prefer --stdin', preg_replace(['/\n|!/', '/ +/'], ['', ' '], $this->tester->getDisplay()));
        $this->assertStringNotContainsString('Option-password1', $this->tester->getDisplay(), 'the password is not displayed');
    }

    public function testPasswordOptionInInteractiveModeAsksNothing(): void
    {
        // No inputs: the command must not ask for the password
        $this->tester->execute(['--password' => 'Option-password1', '--login' => 'jdupont']);

        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('Option-password1', $this->hashOf('jdupont')));
    }

    public function testPasswordOptionWeakPasswordIsRejected(): void
    {
        $this->tester->execute(['--password' => 'weak'], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    public function testPasswordOptionWithAllowWeakPasswordAndMinLength(): void
    {
        $this->tester->execute(['--password' => 'admin', '--allow-weak-password' => true], ['interactive' => false]);
        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('admin', $this->hashOf('admin')));

        $this->tester->execute(['--password' => 'Abc1!x', '--min-length' => '6'], ['interactive' => false]);
        $this->tester->assertCommandIsSuccessful();
        $this->assertTrue(password_verify('Abc1!x', $this->hashOf('admin')));
    }

    public function testEmptyPasswordOptionIsRejected(): void
    {
        $this->tester->execute(['--password' => ''], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $this->tester->getStatusCode());
        $this->assertStringContainsString('cannot be empty', $this->tester->getDisplay());
    }

    public function testPasswordOptionIsExclusive(): void
    {
        foreach ([['--generate' => true], ['--stdin' => true]] as $other) {
            $this->tester->execute(['--password' => 'Option-password1'] + $other, ['interactive' => false]);

            $this->assertSame(Command::INVALID, $this->tester->getStatusCode());
            $this->assertStringContainsString('cannot be used together', $this->tester->getDisplay());
        }

        $this->assertTrue(password_verify(self::OLD_PASSWORD, $this->hashOf('admin')));
    }

    /**
     * Runs the command with --generate and returns the password written on the standard output
     */
    private function generate(array $options = []): string
    {
        $this->tester->execute(['--generate' => true] + $options, ['interactive' => false, 'capture_stderr_separately' => true]);
        $this->tester->assertCommandIsSuccessful();

        return trim($this->tester->getDisplay());
    }

    private function hashOf(string $login): string
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => $login])->getPassword();
    }
}
