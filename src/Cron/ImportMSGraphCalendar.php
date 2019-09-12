<?php

namespace App\Cron;

use App\PlanningBiblio\MSGraphClient;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;

class ImportMSGraphCalendar extends Command {

    use LockableTrait;

	protected function configure () {
		$this->setName('PlanningBiblio:importMSGraphCalendar');
		$this->setDescription("Import a calendar from Microsoft Graph API");
		$this->setHelp("Import a calendar from Microsoft Graph API");
	}

	public function execute (InputInterface $input, OutputInterface $output) {
		$output->writeln("start importMS");

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }

        $tenantid = $_ENV['MS_GRAPH_TENANT_ID'] ?? null;
        $clientid = $_ENV['MS_GRAPH_CLIENT_ID'] ?? null;
        $clientsecret = $_ENV['MS_GRAPH_CLIENT_SECRET'] ?? null;

        if (!$tenantid || !$clientid || !$clientsecret) {
            $output->writeln('At least one of the following is not defined: MS_GRAPH_TENANT_ID, MS_GRAPH_CLIENT_ID, MS_GRAPH_CLIENT_SECRET. Please check you .env.{prod|dev}.local file.');
            return 0;
        }

        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $graph_client = new MSGraphClient($em, $tenantid, $clientid, $clientsecret);
        $graph_client->retrieveEvents();

        $this->release();
		$output->writeln("end");
	}
}

?>
