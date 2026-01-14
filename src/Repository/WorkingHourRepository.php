<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use App\Entity\WorkingHour;

class WorkingHourRepository extends EntityRepository
{

    public function changeCurrent(): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update(WorkingHour::class, 'w')
            ->set('w.actuel', 0)
            ->where('w.debut > CURRENT_DATE() OR w.fin < CURRENT_DATE()')
            ->getQuery()
            ->execute();

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update(WorkingHour::class, 'w')
            ->set('w.actuel', 1)
            ->where('w.debut <= CURRENT_DATE() AND w.fin >= CURRENT_DATE()')
            ->getQuery()
            ->execute();

    }

    public function get($start, $end = null, $valid = true, $perso_id = null)
    {
        $end = $end ?? $start;

        $entityManager = $this->getEntityManager();

        $builder = $entityManager->createQueryBuilder();

        $builder->select('w')
            ->from(WorkingHour::class, 'w')
            ->andWhere('w.debut <= :end')
            ->andWhere('w.fin >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($perso_id) {
            $builder->andWhere('w.perso_id = :perso_id')
                ->setParameter('perso_id', $perso_id);
        }

        if ($valid) {
            $builder->andWhere('w.valide > 0');
        }

        $result = $builder->getQuery()->getResult();

        return $result;
    }

    public function fetch(): void
    {
        // Recherche des services
        $p=new personnel();
        $p->fetch();
        foreach ($p->elements as $elem) {
            $services[$elem['id']]=$elem['service'];
        }

        // Filtre de recherche
        $filter="1";

        // Perso_id
        if ($this->perso_id) {
            $filter.=" AND `perso_id`='{$this->perso_id}'";
        }

        // Date, debut, fin
        $debut=$this->debut;
        $fin=$this->fin;
        $date=date("Y-m-d");
        if ($debut) {
            $fin=$fin?$fin:$date;
            $filter.=" AND `debut`<='$fin' AND `fin`>='$debut'";
        } else {
            $filter.=" AND `fin`>='$date'";
        }


        // Recherche des agents actifs seulement
        $perso_ids=array(0);
        $p=new personnel();
        $p->fetch("nom");
        foreach ($p->elements as $elem) {
            $perso_ids[]=$elem['id'];
        }

        // Recherche avec le nom de l'agent
        if ($this->agent) {
            $perso_ids=array(0);
            $p=new personnel();
            $p->fetch("nom", null, $this->agent);
            foreach ($p->elements as $elem) {
                $perso_ids[]=$elem['id'];
            }
        }

        if (!empty($this->perso_ids)) {
            $perso_ids = $this->perso_ids;
        }

        // Filtre pour agents actifs seulement et recherche avec nom de l'agent
        $perso_ids=implode(",", $perso_ids);
        $filter.=" AND `perso_id` IN ($perso_ids)";

        // Valide
        if ($this->valide) {
            $filter.=" AND `valide`<>0";
        }
  
        // Ignore actuels (pour l'import)
        if ($this->ignoreActuels) {
            $filter.=" AND `actuel`=0";
        }
  
        // Filtre avec ID, si ID, les autres filtres sont effacés
        if ($this->id) {
            $filter="`id`='{$this->id}'";
        }

        $db=new db();
        $db->select("planning_hebdo", "*", $filter, "ORDER BY debut,fin,saisie");
    
        $p=new personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents = $p->elements;
    

        if ($db->result) {
            foreach ($db->result as $elem) {
                $hours = json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                $elem['temps'] = is_array($hours) ? $hours : [];
                $elem['breaktime'] = json_decode(html_entity_decode($elem['breaktime'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                $elem['nom'] = $elem['perso_id']!=0 ? nom($elem['perso_id'], 'nom p', $agents) : "";
                $elem['service']= $elem['perso_id']!=0 ? $services[$elem['perso_id']] : "";
                $this->elements[]=$elem;
            }
        }

        // Tri par date de début, fin et nom des agents
        usort($this->elements, "cmp_debut_fin_nom");

        // Classe les plannings copiés (remplaçant) après les plannings d'origine
        $tab=array();
        foreach ($this->elements as $elem) {
            if (!$elem['remplace']) {
                $tab[]=$elem;
                foreach ($this->elements as $elem2) {
                    if ($elem2['remplace']==$elem['id']) {
                        $tab[]=$elem2;
                    }
                }
            }
        }

        // Merge exception planning into their target.
        if ($this->merge_exception) {
            foreach ($tab as $elem) {
                if ($target = $elem['exception']) {
                    // Searching for target planning.
                    foreach ($tab as $index => $elem2) {
                        if ($elem2['id'] == $target) {
                            $merged = $this->merge($elem, $elem2);
                            $tab[$index] = $merged;
                        }
                    }
                }
            }

            // Clear from exception.
            // Need to do that after last loop
            // to keep proper indexes.
            foreach ($tab as $index => $elem) {
                if ($target = $elem['exception']) {
                    unset($tab[$index]);
                }
            }
        }

        // $tab est vide si on accède directement à un planning copié,
        // on remplace donc $this->elements par $tab seulement si $tab n'est pas vide.
        if (!empty($tab)) {
            $this->elements=$tab;
        }
    }
}
