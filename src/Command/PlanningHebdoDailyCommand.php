<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');
require_once(__DIR__ . '/../../public/include/db.php');

#[AsCommand(
    name: 'app:planning:hebdo_daily',
    description: 'Updates weekly planning records daily by marking the current schedule as active for each employee.',
)]
class PlanningHebdoDailyCommand extends Command
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

        $p=new \planningHebdo();
        $p->debut=date("Y-m-d");
        $p->valide=true;
        $p->ignoreActuels=true;
        $p->fetch();
        foreach ($p->elements as $elem) {
            $id=$elem['id'];
            $perso_id=$elem['perso_id'];
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('actuel'=>0), array('perso_id'=>$perso_id));
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('planning_hebdo', array('actuel'=>1), array('id'=>$id));
        }

        if ($output->isVerbose()){
            $io->success('Weekly planning records have been successfully updated for all employees.');
        }

        return Command::SUCCESS;
    }
}
