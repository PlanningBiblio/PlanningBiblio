<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../public/include/db.php');

#[AsCommand(
    name: 'app:holiday:reset:remainder',
    description: 'Reset the remainders',
)]
class HolidayResetRemainderCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $CSRFToken = CSRFToken();

        // Ajout d'une ligne d'information dans le tableau des congés
        $p = new \personnel();
        $p->supprime = array(0,1);
        $p->fetch();
        if ($p->elements) {
            foreach ($p->elements as $elem) {
                $credits = array();
                $credits['conges_credit'] = $elem['conges_credit'];
                $credits['comp_time'] = $elem['comp_time'];
                $credits['conges_anticipation'] = $elem['conges_anticipation'];
                $credits['conges_reliquat'] = 0;

                $c = new \conges();
                $c->perso_id = $elem['id'];
                $c->CSRFToken = $CSRFToken;
                $c->maj($credits, 'modif', true);
            }
        }

        // Modifie les crédits
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update('personnel', array('conges_reliquat' => '0.00'));

        if ($output->isVerbose()) {
            $io->success('Reset the remainders successfully !');
        }

        return Command::SUCCESS;
    }
}
