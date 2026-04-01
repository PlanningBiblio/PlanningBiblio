<?php

namespace App\Planno\Helper;

use App\Planno\Helper\BaseHelper;
use App\Entity\Config;

class ConfigHelper extends BaseHelper
{

    private $configRepository;

    public function __construct()
    {
        parent::__construct();
        $this->configRepository = $this->entityManager->getRepository(Config::class);
    }

    public function getValue($name): string
    {
        return $this->configRepository->findOneBy(['nom' => $name])->getValue();
    }

    public function getParam($name)
    {
        return $this->configRepository->findOneBy(['nom' => $name]);
    }


    public function getParams($technical): array
    {
        return $this->configRepository->findBy(
                array('technical' => $technical),
                array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
            );
    }

    public function saveConfig($params): string
    {
            $technical = $params['technical'];
            $error = '';

            $configParams = $this->getParams($technical);

            foreach ($configParams as $cp) {
                if (in_array($cp->getType(), ['hidden', 'info'])) {
                    continue;
                }
                // boolean and checkboxes elements.
                if (!isset($params[$cp->getName()])) {
                    $params[$cp->getName()] = $cp->getType() == 'boolean' ? '0' : array();
                }
                $value = $params[$cp->getName()];

                if (is_string($value)) {
                    $value = trim($value);
                }

                // Passwords
                if (substr($cp->getName(), -9) == '-Password') {
                    if ($value == '') {
                        continue;
                    }
                    $value = encrypt($value);
                }
                // Checkboxes
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if ($cp->getType() == 'color') {
                    $value = filter_var($value, FILTER_CALLBACK, ['options' => 'sanitize_color']);
                }

                try {
                    $cp->setValue($value);
                    $this->entityManager->persist($cp);
                }
                catch (Exception $e) {
                    $error = 'Une erreur est survenue pendant la modification de la configuration !';
                }
            }
            $this->entityManager->flush();

            return $error;
    }
}
