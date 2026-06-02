<?php

namespace App\Command;

use App\Planno\Helper\ConfigHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Config;
use App\Planno\MSGraphClient;

#[AsCommand(
    name: 'app:import:ms-graph-calendar',
    description: 'Import calendars from Microsoft Graph API',
)]
class ImportMsGraphCalendarCommand extends Command
{
    use LockableTrait;

    private EntityManagerInterface $entityManager;
    private ConfigHelper $configHelper;

    public function __construct(EntityManagerInterface $entityManager, ConfigHelper $configHelper)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->configHelper = $configHelper;
    }

    protected function configure(): void
    {
        $this
            ->setHelp("Import a calendar from Microsoft Graph API")
            ->addOption('full', null, InputOption::VALUE_OPTIONAL, 'Performs a full import', false)
            ->addOption('stdout', null, InputOption::VALUE_OPTIONAL, 'Also output logs in stdout', false)
            ->addOption('user_id', null, InputOption::VALUE_OPTIONAL, 'Only import for this user', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->lock()) {
            $io->error('The command is already running in another process.');
            return Command::FAILURE;
        }

        $config = $this->configHelper->getAll();

        $tenantid = $config['MSGraph-TenantID'];
        $clientid = $config['MSGraph-ClientID'];
        $clientsecret = $config['MSGraph-ClientSecret'];

        if (!$tenantid || !$clientid || !$clientsecret) {
            $io->error('At least one of the following is not defined: MSGraph-TenantID, MSGraph-ClientID, MSGraph-ClientSecret. Please check your configuration.');
            return Command::FAILURE;
        }

        $graph_client = new MSGraphClient($this->entityManager, $tenantid, $clientid, $clientsecret, $input->getOption('full'), $input->getOption('stdout'), $input->getOption('user_id'), $this->configHelper);
        $graph_client->retrieveEvents();

        $this->release();

        if ($output->isVerbose()) {
            $io->success('Import completed.');
        }

        return Command::SUCCESS;
    }
}
