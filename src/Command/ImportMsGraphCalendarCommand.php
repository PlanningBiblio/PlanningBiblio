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

use App\PlanningBiblio\MSGraphClient;

#[AsCommand(
    name: 'app:import:ms-graph-calendar',
    description: 'Import a calendar from Microsoft Graph API',
)]
class ImportMsGraphCalendarCommand extends Command
{
    use LockableTrait;

    protected function configure(): void
    {
        $this
            ->setHelp("Import a calendar from Microsoft Graph API")
            ->addOption('full', null, InputOption::VALUE_OPTIONAL, 'Performs a full import', false)
            ->addOption('stdout', null, InputOption::VALUE_OPTIONAL, 'Also output logs in stdout', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->lock()) {
            $io->error('The command is already running in another process.');
            return Command::FAILURE;
        }

        $tenantid = $_ENV['MS_GRAPH_TENANT_ID'] ?? null;
        $clientid = $_ENV['MS_GRAPH_CLIENT_ID'] ?? null;
        $clientsecret = $_ENV['MS_GRAPH_CLIENT_SECRET'] ?? null;

        if (!$tenantid || !$clientid || !$clientsecret) {
            $io->error('At least one of the following is not defined: MS_GRAPH_TENANT_ID, MS_GRAPH_CLIENT_ID, MS_GRAPH_CLIENT_SECRET. Please check you .env.{prod|dev}.local file.');
            return Command::FAILURE;
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $graph_client = new MSGraphClient($em, $tenantid, $clientid, $clientsecret, $input->getOption('full'), $input->getOption('stdout'));
        $graph_client->retrieveEvents();

        $this->release();

        if ($output->isVerbose()) {
            $io->success('Import completed.');
        }

        return Command::SUCCESS;
    }
}
