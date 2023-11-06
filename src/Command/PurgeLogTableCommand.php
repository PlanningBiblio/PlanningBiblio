<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\PlanningBiblio\Logger;

#[AsCommand(
    name: 'app:purge:log-table',
    description: 'Purge Planno log table',
)]
class PurgeLogTableCommand extends Command
{
    use LockableTrait;

    protected function configure(): void
    {
        $this
            ->setHelp('
Purge Planno log table.
The delay parameter is mandatory. See https://dev.mysql.com/doc/refman/en/expressions.html#temporal-intervals.
Usage:   php bin/console app:purge:log-table "<DELAY>"
Example: php bin/console app:purge:log-table "12 MONTH"
            ')
            ->addArgument('delay', InputArgument::REQUIRED, 'MySQL interval (ex: 12 MONTH)')
            ->addOption('stdout', null, InputOption::VALUE_OPTIONAL, 'Output result in stdout', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $delay = $input->getArgument('delay');

        if (!$this->lock()) {
            $io->error('The command is already running in another process.');
            return Command::FAILURE;
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();
        // TODO: Now that the model exists, I could be replaced by a query builder.
        $query = "DELETE FROM " . $_ENV['DATABASE_PREFIX'] . "log WHERE timestamp < (NOW() - INTERVAL $delay)";
        $statement = $em->getConnection()->prepare($query);
        $result = $statement->execute();
        $logger = new Logger($em, $input->getOption('stdout'));
        $logger->log("Log table entries older than $delay purged (" . $result->rowCount() . " deleted)", "PurgeLogTable");
        $this->release();

        $io->success("Log table entries older than $delay purged (" . $result->rowCount() . " deleted)");

        return Command::SUCCESS;
    }
}
