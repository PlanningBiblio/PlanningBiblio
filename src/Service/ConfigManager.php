<?php
// src/Service/ConfigManager.php
namespace App\Service;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConfigRepository;
use App\Entity\Config;

class ConfigManager
{
/*
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
*/

    #TODO Demain: utiliser un helper plutôt qu'un service quand même?

#    private ConfigRepository $configRepository;
#    private EntityManager $entityManager;
/*
    public function __construct(ConfigRepository $configRepository, EntityManagerInterface $entityManager)
    {
        $this->configRepository = $configRepository;
        $this->entityManager = $entityManager;
    }
*/

 public function __construct(
        private ConfigRepository $configRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function saveConfig($params): string
    {
            $technical = $params['technical'];
            $error = '';

            $configParams = $this->configRepository->findBy(
                array('technical' => $technical),
                array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
            );

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
