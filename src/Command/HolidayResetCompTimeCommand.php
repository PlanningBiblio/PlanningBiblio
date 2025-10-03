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
require_once __DIR__ . '/../../public/include/function.php';

#[AsCommand(
    name: 'app:holiday:reset-comp-time',
    description: 'Add a short description for your command',
)]
class HolidayResetCompTimeCommand extends Command
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

        require_once __DIR__ . '/../../init/init_entitymanager.php';
        $entityManager = $GLOBALS['entityManager'];

        $CSRFToken = CSRFToken();

        // Ajout d'une ligne d'information dans le tableau des congés
        $agentRepository = $entityManager->getRepository(Agent::class)
        ->findBy(['supprime' => 0], ['nom' => 'ASC']);
        $manager = $entityManager->getRepository(Manager::class)
        ->findAll();

        $agentRepository = $entityManager->getRepository(Agent::class)
        ->findAll();
        foreach ($agentRepository as $p) {
            $p->supprime=array(0,1);
            $credits=array();
            $credits['comp_time'] = 0;
            $credits['conges_credit'] = $p['conges_credit'];
            $credits['conges_anticipation'] = $p['conges_anticipation'];
            $credits['conges_reliquat'] = $p['conges_reliquat'];

            $holidayRepository = $entityManager->getRepository(Holiday::class)
            ->findAll();
            foreach ($holidayRepository as $c) {
                $c->perso_id=$p['id'];
                $c->CSRFToken = $CSRFToken;
                $c->maj($credits, "modif", true);
            }
        }
        //$p=new \personnel();
        // $p->supprime=array(0,1);
        // $p->fetch();
        // if ($p->elements) {
        //     foreach ($p->elements as $elem) {
        //         $credits=array();
        //         $credits['comp_time'] = 0;
        //         $credits['conges_credit'] = $elem['conges_credit'];
        //         $credits['conges_anticipation'] = $elem['conges_anticipation'];
        //         $credits['conges_reliquat'] = $elem['conges_reliquat'];
        //
        //         $c=new \conges();
        //         $c->perso_id=$elem['id'];
        //         $c->CSRFToken = $CSRFToken;
        //         $c->maj($credits, "modif", true);
        //     }
        // }

        // Modifie les crédits
        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update("personnel", "comp_time='0.00'");
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
