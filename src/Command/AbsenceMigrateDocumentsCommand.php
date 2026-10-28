<?php

namespace App\Command;

use App\Entity\AbsenceDocument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../../legacy/Common/function.php';

#[AsCommand(
    name: 'app:absence:migrate-documents',
    description: 'Migrate absence documents files (rename them to a random hash)',
)]
class AbsenceMigrateDocumentsCommand extends Command
{
    private $entityManager;

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

        $entityManager = $this->entityManager;

        $absenceDocumentRepository = $entityManager->getRepository(AbsenceDocument::class);
        $qb = $absenceDocumentRepository->createQueryBuilder('ad');
        $qb->where('ad.filename = ad.storage_id');
        $absenceDocuments = $qb->getQuery()->getResult();

        if (empty($absenceDocuments)) {
            $io->info('All absence documents are already migrated');

            return Command::SUCCESS;
        }

        $io->info(sprintf("%d absence documents to migrate", count($absenceDocuments)));

        $migratedDocumentsCount = 0;
        foreach ($absenceDocuments as $absenceDocument) {
            $from = $absenceDocument->getStoragePath();
            $storageId = bin2hex(random_bytes(16));
            $to = sprintf('%s/%s', $absenceDocument->getStorageDirectory(), $storageId);

            if (!file_exists($from)) {
                $io->error(sprintf('Source file "%s" does not exist (id: %d)', $from, $absenceDocument->getId()));
                continue;
            }
            if (file_exists($to)) {
                $io->error(sprintf('Destination file "%s" already exists (id: %d)', $to, $absenceDocument->getId()));
                continue;
            }

            if (false === rename($from, $to)) {
                $io->error(sprintf('Failed to rename "%s" to "%s" (id: %d)', $from, $to, $absenceDocument->getId()));
                continue;
            }

            $absenceDocument->setStorageId($storageId);
            $this->entityManager->flush();
            $migratedDocumentsCount++;
        }

        if ($migratedDocumentsCount !== count($absenceDocuments)) {
            $io->error('Some documents could not have been migrated and require a manual intervention');

            return Command::FAILURE;
        }

        $io->success('All documents were migrated successfully');

        return Command::SUCCESS;
    }
}
