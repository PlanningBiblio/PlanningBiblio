<?php

namespace App\Cron;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;

use App\PlanningBiblio\Logger;

class PurgeLogTable extends Command {

    use LockableTrait;

    protected function configure () {
        $this->setName('PlanningBiblio:PurgeLogTable');
        $this->setDescription("Purges PlanningBiblio log table");
        $this->setHelp("
Purges PlanningBiblio log table.
The delay parameter is mandatory. See https://dev.mysql.com/doc/refman/en/expressions.html#temporal-intervals.
Usage:   php bin/console PlanningBiblio:PurgeLogTable \"<DELAY>\"
Example: php bin/console PlanningBiblio:PurgeLogTable \"12 MONTH\"
        ");
        $this->addArgument('delay', InputArgument::REQUIRED, 'MySQL interval (ex: 12 MONTH)');
        $this->addOption('stdout', null, InputOption::VALUE_OPTIONAL, 'Output result in stdout', false);
    }

    public function execute (InputInterface $input, OutputInterface $output) {

        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }

        $delay = $input->getArgument('delay');
        $kernel = $this->getApplication()->getKernel();
        $container = $kernel->getContainer();
        $em = $container->get('doctrine')->getManager();
        // TODO: Now that the model exists, I could be replaced by a query builder.
        $query = "DELETE FROM " . $_ENV['DATABASE_PREFIX'] . "log WHERE timestamp < (NOW() - INTERVAL $delay)";
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $logger = new Logger($em, $input->getOption('stdout'));
        $logger->log("Log table entries older than $delay purged (" . $statement->rowCount() . " deleted)", "PurgeLogTable");
        $this->release();
    }
}

?>
