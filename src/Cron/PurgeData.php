<?php

namespace App\Cron;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;

use App\PlanningBiblio\DataPurger;

class PurgeData extends Command {

    use LockableTrait;

    protected function configure () {
        $this->setName('PlanningBiblio:PurgeData');
        $this->setDescription("Purges PlanningBiblio old data");
        $this->setHelp("
Purges PlanningBiblio old data.
The delay parameter is the number of years to purge. It is optional (set to 5 by default)
Usage:   php bin/console PlanningBiblio:PurgeData \"<YEARS>\"
Example: php bin/console PlanningBiblio:PurgeData \"5\"
        ");
        $this->addArgument('delay', InputArgument::OPTIONAL, 'Number of years (5 by default)');
        $this->addOption('stdout', null, InputOption::VALUE_OPTIONAL, 'Output result in stdout', false);
    }

    public function execute (InputInterface $input, OutputInterface $output) {

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }

        $delay = $input->getArgument('delay');
        if ($delay != null) {
            if (!is_numeric($delay)) {
                $output->writeln('The delay argument must be a number of years.');
                return 0;
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
    }
}

?>
