<?php

namespace App\Command;

use App\Entity\AbsenceDocument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../../public/include/function.php';
require_once __DIR__ . '/../../legacy/Class/class.absences.php';
require_once __DIR__ . '/../../legacy/Class/class.personnel.php';

#[AsCommand(
    name: 'app:absence:delete-documents',
    description: 'Supprime les anciens documents d\'absence en fonction de la configuration.',
)]
class AbsenceDeleteDocumentsCommand extends Command
{
    // private EntityManagerInterface $entityManager;
    // private array $config;

    public function __construct()
    {
        parent::__construct();
        // $this->entityManager = $entityManager;
        // $this->config = include __DIR__ . '/../../include/config.php';
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        require_once __DIR__ . '/../../public/include/config.php';
        require_once __DIR__ . '/../../init/init_entitymanager.php';

        $config = $GLOBALS['config'];
        $entityManager = $GLOBALS['entityManager'];

        $CSRFToken = CSRFToken();

        if (!$config['Absences-DelaiSuppressionDocuments'] || $config['Absences-DelaiSuppressionDocuments'] == 0) {
            logs("Suppression des anciens documents d'absences d&eacute;sactiv&eacute;e", "Absences-DelaiSuppressionDocuments", $CSRFToken);
            return Command::SUCCESS;
        }


        $limitdate = new \Datetime();
        $limitdate->sub(new DateInterval('P' . $config['Absences-DelaiSuppressionDocuments'] . 'D'));

        $qb = $entityManager->createQueryBuilder();
        $qb->select('a')
        ->from(AbsenceDocument::class, 'a')
        ->where('a.date < :limitdate')
        ->setParameter('limitdate', $limitdate);

        $absdocs = $qb->getQuery()->getResult();
        foreach ($absdocs as $ad) {
            $ad->deleteFile();
            $entityManager->remove($ad);
        }
        $entityManager->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
