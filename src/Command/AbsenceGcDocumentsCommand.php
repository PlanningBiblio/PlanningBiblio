<?php

namespace App\Command;

use App\Entity\AbsenceDocument;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:absence:gc-documents',
    description: 'Remove database entities referencing non-existing files and files not referenced by a database entity',
    help: <<<'HELP'
        This command finds AbsenceDocument database entities that references
        non-existing files and files in AbsenceDocument::upload_dir() not referenced by
        any AbsenceDocument database entity.

        For each finding you will be asked to confirm deletion by answering by 'yes' or 'y'.
        HELP
)]
class AbsenceGcDocumentsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entityManager = $this->entityManager;

        $fileMap = [];
        $absenceDocuments = $entityManager->getRepository(AbsenceDocument::class)->findAll();
        foreach ($absenceDocuments as $absenceDocument) {
            $storagePath = $absenceDocument->getStoragePath();
            if (!file_exists($storagePath)) {
                $question = sprintf("Absence document #%d references non-existing file %s. Delete database entity ?", $absenceDocument->getId(), $storagePath);
                $deletionConfirmed = $io->confirm($question, false);
                if ($deletionConfirmed) {
                    try {
                        $this->entityManager->remove($absenceDocument);
                        $this->entityManager->flush();
                        $io->success(sprintf('Absence document #%d deleted', $absenceDocument->getId()));
                    } catch (\Exception $e) {
                        $io->error(sprintf('Failed to delete absence document: %s', $e->getMessage()));
                        return Command::FAILURE;
                    }
                }
            } else {
                $fileMap[realpath($storagePath)] = true;
            }
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(AbsenceDocument::upload_dir()));
        $directoriesToRemove = [];
        foreach ($files as $file) {
            if ($file->isFile()) {
                $filepath = $file->getRealpath();
                if (!array_key_exists($filepath, $fileMap)) {
                    $question = sprintf("File %s is not referenced by any absence document. Delete file ?", $filepath);
                    $deletionConfirmed = $io->confirm($question, false);
                    if ($deletionConfirmed) {
                        if (@unlink($filepath) === false) {
                            $error = error_get_last();
                            $io->error(sprintf('Failed to delete file: %s', $error['message']));
                            return Command::FAILURE;
                        }

                        $io->success(sprintf('File %s deleted', $filepath));
                        $directoriesToRemove[] = dirname($filepath);
                    }
                }
            }
        }

        // Remove empty directories
        foreach ($directoriesToRemove as $dirname) {
            @rmdir($dirname);
        }

        return Command::SUCCESS;
    }
}
