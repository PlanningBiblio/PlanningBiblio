<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Model\Manager;
use App\Model\Agent;

class ManagerRepository extends EntityRepository
{
    public function deleteForAgents($agent_ids = array())
    {
        $entityManager = $this->getEntityManager();

        foreach ($agent_ids as $id) {
            $managers = $entityManager->getRepository(Manager::class)
                ->findBy(array('perso_id' => $id));

            foreach ($managers as $manager) {
                $entityManager->remove($manager);
            }
        }
        $entityManager->flush();
    }

    public function addForAgentsLevel1($agent_ids, $manager_ids, $notifications)
    {
        foreach ($agent_ids as $id) {
            $this->addManagers($id, $manager_ids, $notifications);
        }

        return $this;
    }

    public function addForAgentsLevel2($agent_ids, $manager_ids, $notifications)
    {
        foreach ($agent_ids as $id) {
            $this->addManagers($id, $manager_ids, $notifications, 2);
        }

        return $this;
    }

    public function addManagers($agent_id, $manager_ids, $notifications, $level = 1)
    {
        $entityManager = $this->getEntityManager();
        $agent = $entityManager->getRepository(Agent::class)->find($agent_id);


        foreach ($manager_ids as $id) {
            $notification = in_array($id, $notifications) ? 1 : 0 ;
            $agent_manager = $entityManager->getRepository(Agent::class)->find($id);

            $manager = $entityManager->getRepository(Manager::class)
                ->findOneBy(array('perso_id' => $agent_id, 'responsable' => $id));

            // Because we had level one first,
            // if a manager already exists,
            // its a level one.
            if ($manager) {
                $manager->level2(1);
                $manager->notification_level2($notification);
                $entityManager->persist($manager);
                continue;
            }

            $manager = new Manager();
            $manager->perso_id($agent);
            $manager->responsable($agent_manager);
            $manager->level1($level == 1 ? 1 : 0);
            $manager->level2($level == 2 ? 1 : 0);
            $manager->notification_level1($level == 1 ? $notification : 0);
            $manager->notification_level2($level == 2 ? $notification : 0);
            $entityManager->persist($manager);
        }
        $entityManager->flush();
    }
}
