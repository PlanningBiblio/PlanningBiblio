<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Position;
use App\Model\SelectFloor;
use App\Model\SelectGroup;
use App\Model\Skill;

require_once(__DIR__ . '/../../public/postes/class.postes.php');
require_once(__DIR__ . '/../../public/activites/class.activites.php');

class PositionController extends BaseController
{
    /**
     * @Route("/position", name="position.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        //            Affichage de la liste des postes
        $groupe = "Tous";
        $this->templateParams(array('groupe' =>$groupe));

        $nom = $request->get('nom');

        // Contrôle si le poste est utilisé dans un tableau non-supprimé (tables pl_poste_lignes et pl_poste_tab)
        $postes_utilises = array();

        $db = new \db();
        $db->selectInnerJoin(array("pl_poste_lignes","numero"), array("pl_poste_tab","tableau"), array(array("name"=>"poste", "as"=>"poste")), array(), array("type"=>"poste"), array("supprime"=>null));
        if ($db->result) {
            foreach ($db->result as $elem) {
                $postes_utilises[] = $elem['poste'];
              }
        }
        // Sélection des activités
        $activitesTab = array();
        $db = new \db();
        $db->sanitize_string = false;
        $db->select("activites");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $activitesTab[$elem["id"]] = $elem["nom"];
            }
        }

        $p = $this->entityManager->getRepository(Position::class)->findBy(array('supprime' => NULL), array('nom'=>'ASC'));
        $postes = array();
        foreach($p as $poste){
            $postes[] = $poste;
        }

        $nbMultisite = $this->config('Multisites-nombre');
        $this->templateParams(array(
            'multisite'     => $nbMultisite,
            'usedPositions' => $postes_utilises,
            'CSRFSession'   => $GLOBALS['CSRFSession']
        ));

        // Floors and groups
        $floors = $this->entityManager->getRepository(SelectFloor::class);
        $groups = $this->entityManager->getRepository(SelectGroup::class);

        $positions = array();

        foreach ($postes as $id => $value) {
            // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle

            $activites = array();
            $activitesAffichees = array();
            $activitesPoste = $value->activites();

            if (is_array($activitesPoste)) {
                foreach ($activitesPoste as $act) {
                    if (array_key_exists($act, $activitesTab)) {
                        $activites[] = $activitesTab[$act];
                        if (count($activitesAffichees)<3) {
                            $activitesAffichees[] = $activitesTab[$act];
                        }
                    }
                }
            }
            $activites = implode(", ", $activites);
            $activitesAffichees = implode(", ", $activitesAffichees);
            if (count($activitesPoste)>3) {
                $activitesAffichees.=" ...";
            }

            if ($nbMultisite>1) {
                $site = $this->config("Multisites-site{$value->site()}") ? $this->config("Multisites-site{$value->site()}") :"-";
                $new['site'] = $site;
            }
            $new['nom'] =  $value->nom();
            $new['activites'] = $activites;
            $new['activitesAffichees'] = $activitesAffichees;
            $new['id'] = $value->id();
            $new['groupe'] = $groups->find($value->groupe()) ? $groups->find($value->groupe())->valeur() : null;
            $new['etage'] = $floors->find($value->etage()) ? $floors->find($value->etage())->valeur() : null;
            $new['statistiques'] = $value->statistiques();
            $new['bloquant'] = $value->bloquant();
            $new['obligatoire'] = $value->obligatoire();
            $new['position'] = str_replace(['backOffice', 'frontOffice'], ['Back Office', 'Front Office'], $value->position());
            $positions[] = $new;
        }

        $this->templateParams(array('positions'=> $positions));

        return $this->output('position/index.html.twig');
    }

    /**
     * @Route("/position/add", name="position.add", methods={"GET"})
     * @Route("/position/{id}", name="position.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        // Initialisation des variables
        $id = $request->get('id');

        if (is_numeric($id)) {
            $position  =  $this->entityManager->getRepository(Position::class)->find($id);
            $nom =  $position->nom();
            $etage = $position->etage();
            $groupe = $position->groupe();
            $groupe_id = $position->groupe_id();
            $categories  =  $position->categories() ?  : array();
            $site = $position->site();
            $activites = $position->activites();
            $obligatoire = $position->obligatoire() =="Obligatoire"?"checked='checked'":"";
            $renfort = $position->obligatoire() == "Renfort"?"checked='checked'":"";
            $stat1 = $position->statistiques()?"checked='checked'":"";
            $stat2 = !$position->statistiques()?"checked='checked'":"";
            $bloq1 = $position->bloquant()?"checked='checked'":"";
            $bloq2 = !$position->bloquant()?"checked='checked'":"";
            $teleworking1 = $position->teleworking() ? "checked='checked'" : "";
            $teleworking2 = !$position->teleworking() ? "checked='checked'" : "";
            $backOffice = $position->position() == 'backOffice' ? 'checked' : null;
            $frontOffice = $position->position() == 'frontOffice' ? 'checked' : null;
        } else {
            $id = null;
            $nom =  null;
            $etage = null;
            $groupe = null;
            $groupe_id = null;
            $categories = array();
            $site = null;
            $activites = array();
            $obligatoire = 'checked';
            $renfort = null;
            $stat1 = 'checked';
            $stat2 = null;
            $bloq1 = 'checked';
            $bloq2 = null;
            $teleworking1 = null;
            $teleworking2 = 'checked';
            $backOffice = null;
            $frontOffice = null;
        }

        // Floors, groups and skills
        $floors = $this->entityManager->getRepository(SelectFloor::class)->findBy([], ['rang' => 'ASC']);
        $groups = $this->entityManager->getRepository(SelectGroup::class)->findBy([], ['rang' => 'ASC']);
        $skill_list = $this->entityManager->getRepository(Skill::class)->findBy(['supprime' => null], ['nom' => 'ASC']);

        // Used floors
        $used_floors = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a.etage')
           ->from(Position::class,'a')
           ->where($qb->expr()->isNull('a.supprime'))
           ->groupBy('a.etage');

        $query = $qb->getQuery();

        $response = $query->getResult();

        if ($response) {
            foreach ($response as $elem) {
                $used_floors[] = $elem['etage'];
            }
        }

        // Used groups
        $used_groups = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.groupe')
           ->from(Position::class,'p')
           ->where($qb->expr()->isNull('p.supprime'))
           ->groupBy('p.groupe');

        $query = $qb->getQuery();

        $response = $query->getResult();
        if ($response) {
            foreach ($response as $elem) {
                $used_groups[] = $elem['groupe'];
            }
        }

        // Recherche des catégories
        $db = new \db();
        $db->select2("select_categories", "*", "1", "order by rang");
        $categories_list = $db->result;

        $nbSites = $this->config('Multisites-nombre');
        $multisite = array();
        if ($nbSites>1){
            for ($i = 1; $i<= $nbSites; $i++) {
                $selected = $site==$i?"selected='selected'":null;
                $multisite[] = $this->config("Multisites-site{$i}");
                $selectedSites[] = $selected;
            }
        }

        $this->templateParams(array(
            'id'            => $id,
            'nom'           => $nom,
            'etage'         => $etage,
            'groupe'        => $groupe,
            'group_id'      => $groupe_id,
            'categories'    => $categories,
            'site'          => $site,
            'activites'     => $activites,
            'obligatoire'   => $obligatoire,
            'renfort'       => $renfort,
            'stat1'         => $stat1,
            'stat2'         => $stat2,
            'teleworking1'  => $teleworking1,
            'teleworking2'  => $teleworking2,
            'backOffice'    => $backOffice,
            'frontOffice'   => $frontOffice,
            'bloq1'         => $bloq1,
            'bloq2'         => $bloq2,
            'floors'        => $floors,
            'groups'        => $groups,
            'nbSites'       => $nbSites,
            'multisite'     => $multisite,
            'skillList'     => $skill_list,
            'categoriesList'=> $categories_list,
            'usedGroups'    => $used_groups,
            'usedFloors'    => $used_floors,
            'selectedSites' => $selectedSites,
            'CSRFToken'     => $GLOBALS['CSRFSession']
            )
        );
        return $this->output('position/edit.html.twig');
    }

    /**
     * @Route("/position", name="position.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $nom = $request->get('nom');
        $id = $request->get('id');

        if (!$nom) {
            $session->getFlashBag()->add('error',"Le nom est obligatoire");
            if(!$id){
                return $this->redirectToRoute('position.add');
            } else {
                return $this->redirectToRoute('position.edit', array('id' => $id));
            }
        } else {

            $activites = $request->get('activites', []);
            $categories = $request->get('categories', []);
            $site = $request->get('site');
            $bloquant = $request->get('bloquant');
            $statistiques = $request->get('statistiques');
            $teleworking = $request->get('teleworking') ?? 0;
            $etage = $request->get('etage',"");
            $groupe = $request->get('groupe', "");
            $groupe_id = $request->get('group_id', "");
            $obligatoire = $request->get('obligatoire');
            $site = $request->get('site', "");
            $front_back = $request->get('position', "");

            if (!$id){
                $position = new Position;
                $position->nom($nom);
                $position->activites($activites);
                $position->categories($categories);
                $position->bloquant($bloquant);
                $position->statistiques($statistiques);
                $position->teleworking($teleworking);
                $position->etage($etage);
                $position->groupe($groupe);
                $position->groupe_id($groupe_id);
                $position->obligatoire($obligatoire);
                $position->position($front_back);
                $position->site($site);

                try{
                    $this->entityManager->persist($position);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }

                if (isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'ajout du poste " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice', "Le poste a été ajouté avec succès");
                }

            } else {
                $position=$this->entityManager->getRepository(Position::class)->find($id);
                $position->nom($nom);
                $position->activites($activites);
                $position->categories($categories);
                $position->bloquant($bloquant);
                $position->statistiques($statistiques);
                $position->teleworking($teleworking);
                $position->etage($etage);
                $position->groupe($groupe);
                $position->groupe_id($groupe_id);
                $position->obligatoire($obligatoire);
                $position->position($front_back);
                $position->site($site);

                try{
                    $this->entityManager->persist($position);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }

                if(isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de la modification du poste " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice',"Le poste a été modifié avec succès");
                }
            }
        }
    
        return $this->redirectToRoute('position.index');
    }

    /**
     * @Route("/position",name="position.delete", methods={"DELETE"})
     */
     public function delete_position(Request $request, Session $session){

        $id = $request->get('id');
        $p = $this->entityManager->getRepository(Position::class)->find($id);

        $date = new \DateTime();
        $p->supprime($date);
        try{
            $this->entityManager->persist($p);
            $this->entityManager->flush();
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }

        if(isset($error)) {
            $session->getFlashBag()->add('error', "Une erreur est survenue lors de la suppression du poste " );
            $this->logger->error($error);
        } else {
            $session->getFlashBag()->add('notice',"Le poste a bien été supprimé");
            return $this->json("Ok");
        }
     }

}
