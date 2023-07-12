<?php

namespace App\Command;

use App\PlanningBiblio\Framework;
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

        $i = 1;
        foreach ($framework as $f) {

            // Hours
            $data .= "hours$i=[|\n";
            $j = 1;
            if (!empty($f['horaires'])) {
                foreach ($f['horaires'] as $h) {
                    $data .= "$j, {$h['debut']}, {$h['fin']}|\n";
                    $j++;
                }
            }
            $data .= "];\n\n";

            // Positions
            $data .= "positions$i=[|\n";
            $j = 1;
            if (!empty($f['lignes'])) {
                foreach ($f['lignes'] as $l) {
                    if ($l['type'] == 'poste') {
                        $data .= "$j, {$l['poste']}|\n";
                        $j++;
                    }
                }
            }
            $data .= "];\n\n";

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
