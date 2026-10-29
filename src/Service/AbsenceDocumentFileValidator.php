<?php

namespace App\Service;

use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbsenceDocumentFileValidator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
    ) {}

    public function validate(UploadedFile $file): ConstraintViolationListInterface
    {
        $config = $this->entityManager->getRepository(Config::class);
        $mimeTypesList = $config->getValue('Absences-documents-typesMime') ?? '';
        $extensionsList = $config->getValue('Absences-documents-extensions') ?? '';

        $mimeTypes = explode(',', $mimeTypesList);
        $extensions = explode(',', $extensionsList);

        // Warning: This does not validate that the file extension matches its mime type.
        $constraint = new Constraints\File(
            extensions: $extensions,
            mimeTypes: $mimeTypes,
            extensionsMessage: $this->translator->trans('The extension of the file is invalid ({{ extension }}). Allowed extensions are {{ extensions }}.'),
            mimeTypesMessage: $this->translator->trans('The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.'),
        );

        return $this->validator->validate($file, $constraint);
    }
}
