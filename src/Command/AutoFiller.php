<?php

namespace App\Command;

require_once(__DIR__ . '/../../public/include/config.php');
require_once(__DIR__ . '/../../init/init_entitymanager.php');

use App\Model\Agent;
use App\Model\PlanningPosition;
use App\Model\Position;

use App\PlanningBiblio\Framework;
use App\PlanningBiblio\WorkingHours;
use App\PlanningBiblio\Helper\PlanningJobHelper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#use Symfony\Component\HttpFoundation\Request;
use Unirest\Request;

#use App\Controller\PlanningJobController;

class AutoFiller extends Command
{
    public $container;
    protected static $defaultName = 'AutoFiller:Fill';
    protected static $defaultDescription = 'Fills one day';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('date', InputArgument::REQUIRED, 'Date')
            ->addArgument('site', InputArgument::REQUIRED, 'Site')
            ->addArgument('login', InputArgument::OPTIONAL, 'Login')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = $input->getArgument('date');
        $login = $input->getArgument('login');
        $site = $input->getArgument('site');

        if ($date) {
            $io->note(sprintf('You passed an argument: date = %s', $date));
        }

        if ($site) {
            $io->note(sprintf('You passed an argument: site = %s', $site));
        }

        if ($login) {
            $io->note(sprintf('You passed an argument: login = %s', $login));
        } else {
            $login = 999999999;
            $io->note(sprintf('Using default login : %s', $login));
        }

        return $this->autoFill($io, $date, $site);
    }

    private function autoFill(\Symfony\Component\Console\Style\SymfonyStyle $io, $date, $site): int
    {
        $session = $this->container->get('session');
        $f = new Framework();
        $framework = $f->getFromDate($date, $site);
        foreach ($framework as $f) {

            $io->info("1 framework");
            #TODO: call src/Controller/PlanningJobController.php/contextmenu to get all remaining available agents
            # Then, randomly (at first) put an agent from the list, cell by cell
#            $request = new Request(array('hre_debut' => '', 'hre_fin' => '', 'allday' => ''));
   #         $response = \Unirest\Request::get('http://planno.local/planningjob/contextmenu?cellule=3&CSRFToken=326765f7bf129d638ce0f91e9d018dde49f31c09ce846fb9fe906a6bb95bc8d7&date=2025-02-14&debut=11%3A00%3A00&fin=12%3A00%3A00&poste=97&site=1&perso_nom=Elise+H.&perso_id=18');
#            $pjc = new PlanningJobController();
#            $result = $pjc->contextmenu($request);
   #         $io->info($response->code);
   #         $io->info($response->body);


            $planningJobHelper = new PlanningJobHelper();
            $results = $planningJobHelper->getContextMenuInfos($site, $date, $debut, $fin, $perso_id, $perso_nom, $poste, $CSRFToken, $session);

        }


        return 0;
    }

}
