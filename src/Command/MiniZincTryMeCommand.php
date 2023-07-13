<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MiniZincTryMeCommand extends Command
{
    protected static $defaultName = 'MiniZinc:TryMe';
    protected static $defaultDescription = 'Add a short description for your command';

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('a', '-a', InputOption::VALUE_NONE, 'Switche between one solution mode and all solutions mode')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
/**
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }
        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
*/

        $a = $input->getOption('a') ? '-a' : null;

        $process = Process::fromShellCommandline(__DIR__ . "/../../minizinc/current/bin/minizinc $a " . __DIR__ . '/../../minizinc/Model/example.mzn');

        try {
            $process->mustRun();
            $io->success($process->getOutput());
        } catch (ProcessFailedException $exception) {
            $io->error($exception->getMessage());
        }

        return 0;
    }
}
