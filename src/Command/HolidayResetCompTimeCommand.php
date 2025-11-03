<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../../public/include/function.php';
require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../public/include/db.php');

#[AsCommand(
    name: 'app:holiday:reset:comp-time',
    description: 'Reset the compensatory time credits',
)]
class HolidayResetCompTimeCommand extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force: Does not require confirmation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            $message_confirm='Do you really want to delete compansatory time credits ? All users will be affected !';
            $confirm = $io->confirm($message_confirm, false);

            if (!$confirm) {
                $io->warning('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $CSRFToken = CSRFToken();

        $p = new \personnel();
        $p->supprime = array(0,1);
        $p->fetch();
        if ($p->elements) {
            foreach ($p->elements as $elem) {
                $credits=array();
                $credits['comp_time'] = 0;
                $credits['conges_credit'] = $elem['conges_credit'];
                $credits['conges_anticipation'] = $elem['conges_anticipation'];
                $credits['conges_reliquat'] = $elem['conges_reliquat'];

                $c = new \conges();
                $c->perso_id=$elem['id'];
                $c->CSRFToken = $CSRFToken;
                $c->maj($credits, 'modif', true);
            }
        }

        // Modifie les crédits
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update('personnel', 'comp_time="0.00"');

        if ($output->isVerbose()) {
            $io->success('Reset the compensatory time for holiday successfully !');
        }

        return Command::SUCCESS;
    }
}
