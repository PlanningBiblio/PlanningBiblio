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
    protected static $defaultName = 'AutoFiller:Fill';
    protected static $defaultDescription = 'Fills one day';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

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

        $return = $this->autoFill($io, $date, $login, $site);

        return $return;
    }

    private function autoFill($io, $date, $login, $site): int
    {
        $session = $this->container->get('session');
        $f = new Framework();
        $framework = $f->getFromDate($date, $site);

        // Hours and positions for Planno
        $hours = array();
        $positions = array();

        // MiniZinc Tables (Hours, Positions and Grey Cells)
        $data = '';

        // Store all used positions to link skills
        $usedPositions = array();

        $i = 1;
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

    private function test1($io, $date, $login, $site): int
    {

        $f = new Framework();
        $framework = $f->getFromDate($date, $site);

        // Hours and positions for Planno
        $hours = array();
        $positions = array();

        // MiniZinc Tables (Hours, Positions and Grey Cells)
        $data = '';

        // Store all used positions to link skills
        $usedPositions = array();

        $i = 1;
        foreach ($framework as $f) {

            // Hours
            $tab = array();
            if (!empty($f['horaires'])) {

                // Hours for Planno
                $hours[$i] = $f['horaires'];

                // Hours for MiniZinc
                $j = 1;
                foreach ($f['horaires'] as $h) {
                    $tab[] = "$j, '{$h['debut']}', '{$h['fin']}'";
                    $j++;
                }
            }
            // $data .= $this->mZArray("Hours$i", $tab);

            $data .= "NumberOfColumns$i = " . ($j - 1) . ";\n\n";

            // Positions
            $positions[$i] = array();

            $tab = array();
            if (!empty($f['lignes'])) {
                $j = 1;
                foreach ($f['lignes'] as $l) {
                    if ($l['type'] == 'poste') {

                        // Positions for Planno
                        $positions[$i][] = $l['poste'];

                        // Positions for MiniZinc
                        $usedPositions[] = $l['poste'];
                        $tab[] = "$j, {$l['poste']}";
                        $j++;
                    }
                }
            }
            // $data .= $this->mZArray("Positions$i", $tab);

            $data .= "NumberOfRows$i = " . ($j - 1) . ";\n\n";

            // Grey Cells
            $tab = array();
            if (!empty($f['cellules_grises'])) {
                $j = 1;
                foreach ($f['cellules_grises'] as $g) {
                    $tmp = explode('_', $g);
                    $tmp[0]++;
                    $tab[] = "$j, {$tmp[0]}, {$tmp[1]}";
                    $j++;
                }
            }
            // $data .= $this->mZArray("Greys$i", $tab);

            $i++;
        }

        // Positions and Skills
        $positionSkills = $this->entityManager->getRepository(Position::class)->findBy(array('id' => $usedPositions));

        $tab = array(); 
        foreach ($positionSkills as $p) {
            $skills = implode(', ', $p->skills());
            $tab[] = $p->id() . ', ' . $skills;
        }
        // $data .= $this->mZArray('PositionSkills', $tab);


        // Agents and Skills
        $qb = $this->entityManager->createQueryBuilder();

        $query = $qb->select('a.id')
            ->from(Agent::class,'a')
            ->where('a.id > 2')
            ->andWhere($qb->expr()->eq('a.supprime', $qb->expr()->literal('0')))
            ->andWhere($qb->expr()->orX(
                'a.depart >= :date',
                $qb->expr()->eq('a.depart', $qb->expr()->literal('0000-00-00'))
            ))
            ->andWhere('a.arrivee <= :date')
            ->setParameters(array('date' => $date))
            ->getQuery();
        $result = $query->getResult();

        $ids = array();
        if (is_array($result)) {
            foreach ($result as $elem) {
                $ids[] = $elem['id'];
            }
        }

        $agents = $this->entityManager->getRepository(Agent::class)->findBy(array('id' => $ids));

        // Add Agents to the MiniZinc data file
        $agentIds = array();
        foreach ($agents as $a) {
            $agentIds[] = $a->id();
        }
        $data .= 'Agents={' . implode(',', $agentIds) . "};\n\n";

        // Add Agents Skills to the MiniZinc data file
        $tab = array();
        foreach ($agents as $a) {
            $skills = implode(', ', $a->skills());
            $tab[] = $a->id() . ', ' . $skills;
        }

        // $data .= $this->mZArray("AgentSkills", $tab);

        // Agents Working Hours
        $workingHours = WorkingHours::getByDate($date);

        $tab = array();
        foreach ($workingHours as $w) {
            $tmp = $w->agentId;
            foreach ($w->workingHours as $wh) {
                if (!empty($wh)) {
                    $tmp .= ", '$wh'";
                }
            }
            $tab[] = $tmp;
        }
        // $data .= $this->mZArray("WorkingHours", $tab);

        // Write the MiniZinc data file
        $path = __DIR__ . '/../../';

        $filesystem = new Filesystem();
        $file = "{$path}var/MiniZinc/data.dzn";

        try {
            $filesystem->dumpFile($file, $data);
        } catch (IOExceptionInterface $exception) {
            $io->error("An error occurred while creating the file $file");
            return 1;
        }

        $io->success("The file $file has been created");

        // Execute MiniZinc
        $process = Process::fromShellCommandline("{$path}minizinc/current/bin/minizinc {$path}src/MiniZinc/Model/OneDay.mzn -d {$path}var/MiniZinc/data.dzn --json-stream");

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
            return 1;
        }

        $output = json_decode($process->getOutput());

        if ($output->type != 'solution') {
            $io->error('No solution found');
            return 1;
        }
       
        $solution = $this->phpArray($output->output->dzn);
        
        $result = json_encode(array(
            'solution' => $solution,
            'hours' => $hours,
            'positions' => $positions,
        ));

        $io->success("\n###RESULT###\n" . $result);

        $date = \Datetime::createFromFormat('Y-m-d', $date);

        $p = $this->entityManager->getRepository(PlanningPosition::class)->findBy(array(
            'date' => $date,
            'site' => $site,
        ));

        // Check if the planning is empty
        if (!empty($p)) {
            $io->error('The planning is not empty');
            return 1;
        }

        // Complete the planning
        $i = 0;
        foreach ($solution as $row) {

            // TODO: We are only using the first table for the moment ($position[1] and $hours[1])
            $position = $positions[1][$i];

            $j = 0;
            foreach ($row as $column) {

                $agent = $column;
                $start = \Datetime::createFromFormat('H:i:s', $hours[1][$j]['debut']);
                $end = \Datetime::createFromFormat('H:i:s', $hours[1][$j]['fin']);

                $p = new PlanningPosition();
                $p->date($date);
                $p->perso_id($agent);
                $p->poste($position);
                $p->absent(0);     // TODO Add default value on model
                $p->chgt_login($login);
                $p->chgt_time(new \DateTime()); // TODO Add default value on model
                $p->debut($start);
                $p->fin($end);
                $p->site($site);
    
                $this->entityManager->persist($p);
                $this->entityManager->flush();

                $j++;
            }

            $i++;
        }

        return 0;
    }

    /**
     * mZArray create a MiniZinc array from a PHP array
     * @param String $var : variable name
     * @param Array $array : PHP array
     * @return String : MiniZinc array
     */
    private function mZArray(String $var, Array $array)
    {
        return "$var =\n[| " . implode("\n | ", $array) . "\n |];\n\n";
    } 

    /**
     * @return mixed[]
     */
    private function phpArray(String $input): array
    {
        $output = explode("\n", $input);

        $last = array_key_last($output);
        unset($output[0]);
        unset($output[$last - 1]);
        unset($output[$last]);

	$func = function(String $input) : Array {
            $var = str_replace(array('[','|',' '), null, $input);
            return (explode(',', $var));
        };

        $output = array_map($func, $output);

        return $output;
    }

}
