<?php

/**
TODO : pas de saut de ligne avant le ]
TODO : toute les lignes d'un même tableau doivent avoir la même longueur
*/

namespace App\Command;

require_once(__DIR__ . '/../../public/include/config.php');
require_once(__DIR__ . '/../../init/init_entitymanager.php');

use App\Model\Agent;
use App\Model\Position;
use App\PlanningBiblio\Framework;
use App\PlanningBiblio\WorkingHours;
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

class MiniZincOneDayCommand extends Command
{
    protected static $defaultName = 'MiniZinc:OneDay';
    protected static $defaultDescription = 'Add a short description for your command';

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = $input->getArgument('date');
        $site = $input->getArgument('site');

        if ($date) {
            $io->note(sprintf('You passed an argument: date = %s', $date));
        }

        if ($site) {
            $io->note(sprintf('You passed an argument: site = %s', $site));
        }

        $f = new Framework();
        $framework = $f->getFromDate($date, $site);

        // MiniZinc Tables (Hours, Positions and Grey Cells)
        $data = '';

        // Store all used positions to link skills
        $usedPositions = array();

        $i = 1;
        foreach ($framework as $f) {

            // Hours
            $data .= "hours$i=[|\n";
            $j = 1;
            if (!empty($f['horaires'])) {
                foreach ($f['horaires'] as $h) {
                    $data .= "$j, '{$h['debut']}', '{$h['fin']}'|\n";
                    $j++;
                }
            }
            $data .= "];\n\n";

            $data .= "NumberOfColumns$i = " . ($j - 1) . ";\n\n";

            // Positions
            $data .= "positions$i=[|\n";
            $j = 1;
            if (!empty($f['lignes'])) {
                foreach ($f['lignes'] as $l) {
                    if ($l['type'] == 'poste') {
                        $usedPositions[] = $l['poste'];
                        $data .= "$j, {$l['poste']}|\n";
                        $j++;
                    }
                }
            }
            $data .= "];\n\n";

            $data .= "NumberOfRows$i = " . ($j - 1) . ";\n\n";

            // Grey Cells
            $data .= "greys$i=[|\n";
            $j = 1;
            if (!empty($f['cellules_grises'])) {
                foreach ($f['cellules_grises'] as $g) {
                    $tab = explode('_', $g);
                    $tab[0]++;
                    $data .= "$j, {$tab[0]}, {$tab[1]}|\n";
                    $j++;
                }
            }
            $data .= "];\n\n";

            $i++;
        }

        // Positions and Skills
        $positions = $this->entityManager->getRepository(Position::class)->findBy(array('id' => $usedPositions));
        $data .= "positionSkills=[|\n";
        foreach ($positions as $p) {
            $skills = implode(', ', $p->skills());
            $data .= $p->id() . ', ' . $skills . "|\n";
        }
        $data .= "];\n\n";


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
        $data .= "agentSkills=[|\n";
        foreach ($agents as $a) {
            $skills = implode(', ', $a->skills());
            $data .= $a->id() . ', ' . $skills . "|\n";
        }
        $data .= "];\n\n";

        // Agents Working Hours
        $workingHours = WorkingHours::getByDate($date);
        $data .= "workingHours=[|\n";
        foreach ($workingHours as $w) {
            $data .= $w->agentId;
            foreach ($w->workingHours as $wh) {
                if (!empty($wh)) {
                    $data .= ", '$wh'";
                }
            }
            $data .= "|\n";
        }
        $data .= "];\n\n";


        $filesystem = new Filesystem();
        $file = __DIR__ . '/../../var/MiniZinc/data.dzn';

        try {
            $filesystem->dumpFile($file, $data);
        } catch (IOExceptionInterface $exception) {
            $io->error("An error occurred while creating the file $file");
        }

        $io->success("The file $file has been created");

        return 0;
    }
}
