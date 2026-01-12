<?php

namespace App\Command;

use App\Entity\Agent;
use App\Entity\Holiday;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../../legacy/Common/function.php';
require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');

#[AsCommand(
    name: 'app:holiday:reset:remainder',
    description: 'Reset the remainder credits',
)]
class HolidayResetRemainderCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
            $message_confirm='Do you really want to delete remainders credits ? All users will be affected !';
            $confirm = $io->confirm($message_confirm, false);

            if (!$confirm) {
                $io->warning('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

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

                $this->entityManager->getRepository(Holiday::class)->insert($elem['id'], $credits, 'update', true);
            }
        }

        // Modifie les crédits
        $this->entityManager->getRepository(Agent::class)->holidayResetRemainder();

        $this->entityManager->flush();

        if ($output->isVerbose()) {
            $io->success('Reset the remainders successfully !');
        }

        return Command::SUCCESS;
    }
}
