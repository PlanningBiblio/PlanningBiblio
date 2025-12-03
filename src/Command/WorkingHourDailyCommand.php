<?php

namespace App\Command;

use App\Entity\Workinghour;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__ . '/../../legacy/Common/function.php';
require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');

#[AsCommand(
    name: 'app:workinghour:daily',
    description: 'Execute daily tasks to update working hours',
)]
class WorkingHourDailyCommand extends Command
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $p=new \planningHebdo();
        $p->debut=date("Y-m-d");
        $p->valide=true;
        $p->ignoreActuels=true;
        $p->fetch();
        foreach ($p->elements as $elem) {
            $id=$elem['id'];
            $perso_id=$elem['perso_id'];
            $planningHebdoActuel = $this->entityManager->getRepository(Workinghour::class)->findBy(['perso_id' => $perso_id]);
            foreach($planningHebdoActuel AS $pla) {
                $pla->setCurrent(0);
                $this->entityManager->persist($pla);
            }
            $planningHebdo=$this->entityManager->getRepository(Workinghour::class)->find($id);
            if($planningHebdo) {
                $planningHebdo->setCurrent(1);
                $this->entityManager->persist($planningHebdo);
            }
        }
        $this->entityManager->flush();

        if ($output->isVerbose()) {
            $io->success('Weekly planning records have been successfully updated for all employees.');
        }

        return Command::SUCCESS;
    }
}
