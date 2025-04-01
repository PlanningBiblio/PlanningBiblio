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

        $config = $GLOBALS['config'];

        $tenantid = $config['MSGraph-TenantID'] ?? null;
        $clientid = $config['MSGraph-ClientID'] ?? null;
        $clientsecret = $config['MSGraph-ClientSecret'] ?? null;

        if (!$tenantid || !$clientid || !$clientsecret) {
            $io->error('At least one of the following is not defined: MSGraph-TenantID, MSGraph-ClientID, MSGraph-ClientSecret. Please check your configuration.');
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
