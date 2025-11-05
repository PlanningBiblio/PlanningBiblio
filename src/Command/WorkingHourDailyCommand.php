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

require_once __DIR__ . '/../../public/include/function.php';
require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');
require_once(__DIR__ . '/../../public/include/db.php');

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

        $CSRFToken = CSRFToken();

        // $p=new \planningHebdo();
        // $p->debut=date("Y-m-d");
        // $p->valide=true;
        // $p->ignoreActuels=true;
        // $p->fetch();
        // foreach ($p->elements as $elem) {
        //     $id=$elem['id'];
        //     $perso_id=$elem['perso_id'];
        //     $db=new \db();
        //     $db->CSRFToken = $CSRFToken;
        //     $db->update('planning_hebdo', array('actuel'=>0), array('perso_id'=>$perso_id));
        //     $db=new \db();
        //     $db->CSRFToken = $CSRFToken;
        //     $db->update('planning_hebdo', array('actuel'=>1), array('id'=>$id));
        // }

        $workinghour = $this->entityManager->getRepository(Workinghour::class)->findBy(['debut' => new \DateTime(), 'valide' => true, 'actuel' => 0]);
        foreach ($workinghour as $elem) {
            $id=$elem['id'];
            $perso_id=$elem['perso_id'];
            $planning_hebdo = $this->entityManager->getRepository();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('actuel'=>0), array('perso_id'=>$perso_id));
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('actuel'=>1), array('id'=>$id));
        }

        if ($output->isVerbose()) {
            $io->success('Weekly planning records have been successfully updated for all employees.');
        }

        return Command::SUCCESS;
    }
}
