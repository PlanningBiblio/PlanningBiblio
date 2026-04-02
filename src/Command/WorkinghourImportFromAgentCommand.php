<?php

namespace App\Command;

use App\Entity\Agent;
use App\Entity\ConfigParam;
use App\Entity\WorkingHour;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:workinghour:import-from-agent',
    description: 'Copy working hours from Agent::workingHours to WorkingHour::workingHours',
)]
class WorkinghourImportFromAgentCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Start date, format YYYY-MM-DD')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'End date, format YYYY-MM-DD')
            ->addOption('purgeWorkingHours', null, InputOption::VALUE_NONE, 'Purge the workingHours table before ?')
            ->addOption('purgeAgentWorkingHours', null, InputOption::VALUE_NONE, 'Purge the workingHours from the agent table after ?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $start = $input->getoption('start');
        $end = $input->getoption('end');
        $purgeWorkingHours = $input->getoption('purgeWorkingHours');
        $purgeAgents = $input->getoption('purgeAgentWorkingHours');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)
            or !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)
        ) {
            $io->error('The options "start" and "end" are required and must be dates in YYYY-MM-DD format.');
            return Command::FAILURE;
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        if ($purgeWorkingHours) {
            $io->info('Purging WorkingHours ...');
            $workingHours = $entityManager->getRepository(WorkingHour::class)->findAll();
            foreach ($workingHours as $wh) {
                $entityManager->remove($wh);
            }
            $entityManager->flush();
        }

        $agents = $entityManager->getRepository(Agent::class)->findAll();
        $numberOfWeeks = $entityManager->getRepository(ConfigParam::class)->findOneBy(['nom' => 'nb_semaine'])->getValue();
        $start = \DateTime::createFromFormat('Y-m-d', $start);
        $end = \DateTime::createFromFormat('Y-m-d', $end);

        $io->info('Copy WorkingHours ...');

        foreach ($agents as $agent) {
            $workingHours = json_decode($agent->getWorkingHours(), true);
            $isEmpty = true;

            if (is_array($workingHours)) {
                foreach ($workingHours as $wh) {
                    if (is_array($wh)) {
                        foreach ($wh as $w) {
                            if (!empty($w)) {
                                $isEmpty = false;
                                break;
                            }
                        }
                    }
                }
            }

            if ($isEmpty) {
                continue;
            }

            $wh = new WorkingHour();
            $wh->setUser($agent->getId());
            $wh->setStart($start);
            $wh->setEnd($end);
            $wh->setWorkingHours($agent->getWorkingHours());
            $wh->setValidLevel1(1);
            $wh->setValidLevel2(1);
            $wh->setValidLevel1Date(new \DateTime());
            $wh->setValidLevel2Date(new \DateTime());
            $wh->setNumberOfWeeks($numberOfWeeks);
            $entityManager->persist($wh);
        }

        $entityManager->flush();

        if ($purgeAgents) {
            $io->info('Purging WorkingHours from the agent table ...');
            $agents = $entityManager->getRepository(Agent::class)->findAll();
            foreach ($agents as $agent) {
                $agent->setWorkingHours('[]');
                $entityManager->persist($agent);
            }
            $entityManager->flush();
        }

        $io->info('Set PlanningHebdo config.');
        $config = $entityManager->getRepository(ConfigParam::class)->findOneBy(['nom' => 'PlanningHebdo']);
        $config->setValue('1');
        $entityManager->flush();

        $io->success('The workings hours were copied.');

        return Command::SUCCESS;
    }
}
