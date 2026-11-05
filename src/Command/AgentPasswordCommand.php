<?php

namespace App\Command;

use App\Entity\Agent;
use App\Entity\Config;
use App\Service\PasswordPolicy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:agent:password',
    description: 'Change the password of an agent (the "admin" account by default).',
)]
class AgentPasswordCommand extends Command
{
    // password_hash(PASSWORD_BCRYPT) only uses the first 72 bytes of the password
    private const BCRYPT_MAX_LENGTH = 72;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordPolicy $passwordPolicy,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('login', 'l', InputOption::VALUE_REQUIRED, 'Login of the agent', 'admin')
            ->addOption('generate', 'g', InputOption::VALUE_NONE, 'Generate a random password and display it once')
            ->addOption('stdin', null, InputOption::VALUE_NONE, 'Read the password from the standard input (first line)')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'New password, without interaction (visible in the shell history and in the process list: prefer --stdin)')
            ->addOption('allow-weak-password', null, InputOption::VALUE_NONE, 'Do not apply the complexity rules (test instances only)')
            ->addOption('min-length', 'm', InputOption::VALUE_REQUIRED, 'Minimum length of the password (overrides the parameter Auth-PasswordLength)')
            ->setHelp(<<<'HELP'
Change the password of an agent. The "admin" account is used by default
(option --login to choose another agent).

<comment>Ways to give the password</comment> (only one at a time)

  Interactive mode (recommended): the password is typed twice and hidden.
    <info>php bin/console app:agent:password</info>
    <info>php bin/console app:agent:password --login=jdupont</info>

  --password: without interaction. Simple, but the password may remain in the
  shell history and be visible in the process list (ps).
    <info>php bin/console app:agent:password --password='My-new-password1' -n</info>

  --stdin: without interaction, read from the standard input (first line).
  Recommended for scripts and shared servers.
    <info>printf '%s' "$NEW_PASSWORD" | php bin/console app:agent:password --stdin -n</info>

  --generate: random password complying with the rules. Only the password is
  written on the standard output, so that it can be captured by a script.
    <info>NEW_PASSWORD=$(php bin/console app:agent:password --generate -n)</info>

<comment>Rules</comment>

  The password must contain at least Auth-PasswordLength characters (8 by
  default), one digit, one uppercase letter, one lowercase letter and one
  special character.

  --min-length=N overrides Auth-PasswordLength (1 to 72: bcrypt only uses the
  first 72 bytes). With --generate, it is the minimum length of the password.
    <info>php bin/console app:agent:password --min-length=12</info>

  --allow-weak-password (test instances only) does not apply the complexity
  rules. If --min-length is also given, the length is still checked. The
  password must never be empty.
    <info>php bin/console app:agent:password --password=admin --allow-weak-password -n</info>

The password is used by the Planno local authentication (SQL): with the
authentication modes SQL, LDAP-SQL, CAS-SQL and OpenIDConnect-SQL, and always
for the "admin" account, it allows logging in. It is not used by the modes
LDAP, CAS and OpenIDConnect alone.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // Messages go to the error output: with --generate, the standard output only contains the password
        $messages = $io->getErrorStyle();

        $login = trim((string) $input->getOption('login'));
        $generate = (bool) $input->getOption('generate');
        $fromStdin = (bool) $input->getOption('stdin');
        $passwordOption = $input->getOption('password');
        $allowWeakPassword = (bool) $input->getOption('allow-weak-password');
        $minLengthOption = $input->getOption('min-length');

        // 1. Options (before any database access)
        if (count(array_filter([$generate, $fromStdin, $passwordOption !== null])) > 1) {
            $messages->error('The options --generate, --stdin and --password cannot be used together.');
            return Command::INVALID;
        }

        if ($minLengthOption !== null
            && (!ctype_digit((string) $minLengthOption) || (int) $minLengthOption < 1 || (int) $minLengthOption > self::BCRYPT_MAX_LENGTH)
        ) {
            $messages->error(sprintf('The option --min-length must be an integer between 1 and %d.', self::BCRYPT_MAX_LENGTH));
            return Command::INVALID;
        }

        if (!$generate && !$fromStdin && $passwordOption === null && !$input->isInteractive()) {
            $messages->error('No password given: use the interactive mode, --password, --stdin or --generate.');
            return Command::INVALID;
        }

        // 2. Agent
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => $login]);
        if (!$agent) {
            $messages->error(sprintf('No agent found with the login "%s".', $login));
            return Command::FAILURE;
        }

        if ((int) $agent->getDeletion() !== 0) {
            $messages->warning(sprintf('The agent "%s" is deleted: the new password will not allow logging in.', $login));
        }

        // 3. Password
        $minimumLength = $minLengthOption !== null
            ? (int) $minLengthOption
            : max(1, (int) ($this->configValue('Auth-PasswordLength') ?: PasswordPolicy::DEFAULT_MINIMUM_LENGTH));

        if ($generate) {
            $password = $this->passwordPolicy->generate(max(16, $minimumLength));
        } elseif ($fromStdin) {
            $password = $this->readFromStdin($input);
        } elseif ($passwordOption !== null) {
            $password = (string) $passwordOption;
            $messages->note('The password given with --password may remain in the shell history and be visible in the process list. Prefer --stdin on shared servers.');
        } else {
            $password = $this->askPassword($messages);
            if ($password === null) {
                return Command::FAILURE;
            }
        }

        // 4. Rules
        if ($password === '') {
            $messages->error('The password cannot be empty.');
            return Command::FAILURE;
        }

        if (!$allowWeakPassword && !$this->passwordPolicy->isComplexEnough($password, $minimumLength)) {
            $messages->error(sprintf(
                'The password is too weak: it must contain at least %d characters, one digit, one uppercase letter, one lowercase letter and one special character.',
                $minimumLength
            ));
            return Command::FAILURE;
        }

        if ($allowWeakPassword) {
            if ($minLengthOption !== null && strlen($password) < $minimumLength) {
                $messages->error(sprintf('The password must contain at least %d characters (--min-length).', $minimumLength));
                return Command::FAILURE;
            }

            if (!$this->passwordPolicy->isComplexEnough($password, $minimumLength)) {
                $messages->warning('The complexity rules are not applied (--allow-weak-password): use this option on test instances only.');
            }
        }

        if (strlen($password) > self::BCRYPT_MAX_LENGTH) {
            $messages->warning(sprintf('Only the first %d bytes of the password are taken into account (bcrypt).', self::BCRYPT_MAX_LENGTH));
        }

        // 5. Save
        $agent->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $this->entityManager->flush();

        if ($generate) {
            $messages->success(sprintf('The password of "%s" has been changed. New password (displayed only once):', $login));
            $output->writeln($password);
        } else {
            $messages->success(sprintf('The password of "%s" has been changed.', $login));
        }

        return Command::SUCCESS;
    }

    private function askPassword(SymfonyStyle $io): ?string
    {
        $question = new Question('New password');
        $question->setHidden(true);
        // Never display the password: fail instead if the terminal cannot hide the input
        $question->setHiddenFallback(false);
        $password = (string) $io->askQuestion($question);

        // Empty password: rejected by execute(), no need to confirm it
        if ($password === '') {
            return '';
        }

        $confirmation = new Question('Confirm the new password');
        $confirmation->setHidden(true);
        $confirmation->setHiddenFallback(false);

        if ($password !== (string) $io->askQuestion($confirmation)) {
            $io->error('The passwords do not match.');
            return null;
        }

        return $password;
    }

    private function readFromStdin(InputInterface $input): string
    {
        $stream = ($input instanceof StreamableInputInterface && $input->getStream()) ? $input->getStream() : STDIN;

        return rtrim((string) fgets($stream), "\r\n");
    }

    private function configValue(string $name): mixed
    {
        $param = $this->entityManager->getRepository(Config::class)->getParam($name);

        return $param ? $param->getValue() : null;
    }
}
