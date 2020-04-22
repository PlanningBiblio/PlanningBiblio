<?php

namespace App\Cron;

use App\PlanningBiblio\GoogleClient;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;

class ImportGoogleCalendar extends Command {

    use LockableTrait;

	protected function configure () {
		$this->setName('PlanningBiblio:importGoogeCalendar');
		$this->setDescription("Import a calendar from Google API");
		$this->setHelp("Import a calendar from Google API");
	}

	public function execute (InputInterface $input, OutputInterface $output) {

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $google_client = new GoogleClient($em);
        $google_client->test();

        $this->release();
	}
}

?>
