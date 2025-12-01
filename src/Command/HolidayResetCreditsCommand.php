<?php

namespace App\Command;

use App\Entity\Agent;
use App\Entity\Config;
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
    name: 'app:holiday:reset:credits',
    description: 'Reset the holiday credits',
)]
class HolidayResetCreditsCommand extends Command
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
            $message_confirm='Do you really want to delete holidays credits ? All users will be affected !';
            $confirm = $io->confirm($message_confirm, false);

            if (!$confirm) {
                $io->warning('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $config = $this->entityManager->getRepository(Config::class)->getAll();
        $transferCompTime = (bool) !empty($config['Conges-transfer-comp-time']);

        $CSRFToken = CSRFToken();

        $p = new \personnel();
        $p->supprime = array(0,1);
        $p->fetch();
        if ($p->elements) {
            foreach ($p->elements as $elem) {
                $credits = array();
                $credits['conges_credit'] = floatval($elem['conges_annuel']) - floatval($elem['conges_anticipation']);
                $credits['conges_anticipation'] = 0;

                if ($transferCompTime) {
                    $credits['conges_reliquat'] = floatval($elem['conges_credit']) + floatval($elem['comp_time']);
                    $credits['comp_time'] = 0;
                } else {
                    $credits['conges_reliquat'] = $elem['conges_credit'];
                    $credits['comp_time'] = $elem['comp_time'];
                }

                $c = new \conges();
                $c->perso_id = $elem['id'];
                $c->CSRFToken = $CSRFToken;
                $c->maj($credits, 'modif', true);
            }
        }

        // Modifie les crédits
        $agents = $this->entityManager->getRepository(Agent::class);

        if ($transferCompTime) {
            $agents->holidayCreditAndCompTimeToRemainder();
        } else {
            $agents->holidayCreditToRemainder();
        }

        if ($transferCompTime) {
            $agents->holidayResetCompTime();
        }

        $agents->holidayResetCredit();

        $this->entityManager->flush();

        if ($output->isVerbose()) {
            $io->success('Reset the credits for holiday successfully!');
        }

        return Command::SUCCESS;
    }
}
