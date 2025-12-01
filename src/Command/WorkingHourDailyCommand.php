<?php

namespace App\Command;

use App\Entity\Workinghour;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->entityManager->getRepository(Workinghour::class)->changeCurrent();

        $this->entityManager->flush();

        if ($output->isVerbose()) {
            $io->success('Weekly planning records have been successfully updated for all employees.');
        }

        return Command::SUCCESS;
    }
}
