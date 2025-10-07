<?php

namespace App\Command;

use App\Entity\Config;
use App\Entity\AbsenceDocument;
use Doctrine\ORM\EntityManagerInterface;
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
    protected $entityManager;

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

        require_once __DIR__ . '/../../init/init_entitymanager.php';

        $config = $this->entityManager->getRepository(Config::class)->getAll();
        $delay = $config['Absences-DelaiSuppressionDocuments'];

        $CSRFToken = CSRFToken();

        if (empty($delay)) {
            $message = 'Suppression des anciens documents d\'absences désactivée';
            \logs($message, 'Absences-DelaiSuppressionDocuments', $CSRFToken);
            $io->warning($message);
            return Command::SUCCESS;
        }

        $limitdate = new \Datetime();
        $limitdate->sub(new \DateInterval('P' . $delay . 'D'));

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
        ->from(AbsenceDocument::class, 'a')
        ->where('a.date < :limitdate')
        ->setParameter('limitdate', $limitdate);

        $absdocs = $qb->getQuery()->getResult();
        foreach ($absdocs as $ad) {
            $ad->deleteFile();
            $this->entityManager->remove($ad);
        }
        $this->entityManager->flush();

        if ($output->isVerbose()){
            $io->success('Absences documents have been deleted');
        }

        return Command::SUCCESS;
    }
}
