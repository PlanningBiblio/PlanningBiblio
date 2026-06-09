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

use App\Planno\DataPurger;

#[AsCommand(
    name: 'app:purge:data',
    description: 'Purge Planno old data',
)]
class PurgeDataCommand extends Command
{
    use LockableTrait;

    protected function configure(): void
    {
        $this
            ->setHelp('
Purge Planno old data.
The delay parameter is the number of years to purge. It is optional (set to 5 by default)
Usage:   php bin/console app:purge:data "<YEARS>"
Example: php bin/console app:purge:data "5"
            ')
            ->addArgument('delay', InputArgument::OPTIONAL, 'Number of years (5 by default)')
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

        if ($delay != null) {
            if (!is_numeric($delay)) {
                $io->error('The delay argument must be a number of years.');
                return Command::FAILURE;
            }
        } else {
            $delay = 5;
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $dataPurger = new DataPurger($em, $delay, $input->getOption('stdout'));
        $dataPurger->purge();

        $this->release();

        if ($output->isVerbose()) {
            $io->success('Purge completed.');
        }

        return Command::SUCCESS;
    }
}
