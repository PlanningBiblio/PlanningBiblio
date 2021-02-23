<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\ClosingDay;

require_once(__DIR__. '/../../public/include/feries.php');

class ClosingDayController extends BaseController
{

    /**
     * @Route("/closingday", name="closingday.index", methods={"GET"})
     */
    public function index(Request $request){
        // Initalisation des variables
        $annee_courante = date("n") < 9 ? (date("Y")-1)."-".(date("Y")) : (date("Y"))."-".(date("Y")+1);
        $annee_suivante = date("n") < 9 ? (date("Y"))."-".(date("Y")+1) : (date("Y")+1)."-".(date("Y")+2);

        $annee_select = $request->get("annee") ?? (isset($_SESSION['oups']['anneeFeries']) ? $_SESSION['oups']['anneeFeries'] : $annee_courante);
        $_SESSION['oups']['anneeFeries'] = $annee_select;

        $annees = array();

        $em = $this->entityManager;

        $db = $em->createQueryBuilder();
        $db->select('c.annee')
           ->from(ClosingDay::class,'c')
           ->groupBy('c.annee');
        $res = $db->getQuery();
        $j = $res->getResult();
        if ($j){
            foreach ($j as $elem){
                $annees[] = $elem['annee'];
            }
        }

        if (!in_array($annee_suivante, $annees)) {
            $annees[] = $annee_suivante;
        }
        if (!in_array($annee_courante, $annees)) {
            $annees[] = $annee_courante;
        }

        sort($annees);

        // Recherche des jours fériés enregistrés dans la base de données et avec la fonction jour_ferie

        $j = $em->getRepository(ClosingDay::class)->findBy(array("annee"=>$annee_select),  array('jour' => 'ASC'));
        $jours = array();
        $days = array();

        if($j){
            foreach($j as $res){
                $jours[] = $res;
            }

            // Affichage des jours fériés enregistrés
            $i = 0;
            foreach ($jours as $elem) {
                if ($elem->ferie()){
                $ferie = true;
                }else{
                    $ferie = false;
                }
                if ($elem->fermeture()){
                    $fermeture = true;
                }else{
                    $fermeture = false;
                }
                $date = dateFr($elem->jour()->format('Y-m-d H:i:s'));
                $commentaire = $elem->commentaire();
                $nom = $elem->nom();
                $days[] = array(
                    "holiday" => $ferie,
                    "closed"  => $fermeture,
                    "date"    => $date,
                     "comment" => $commentaire,
                    "name"    => $nom,
                    "number"  => $i
                );
                $i++;
            }
        } else {
            $debut = substr($annee_select, 0, 4)."-09-01";
            $fin = (substr($annee_select, 0, 4)+1)."-08-31";
            $tmp = array();
            foreach ($jours as $elem) {
                $tmp[]=$elem['jour'];
            }

            for ($date = $debut; $date < $fin; $date = date("Y-m-d", strtotime("+1 day", strtotime($date)))) {
                if (jour_ferie($date)) {
                    if (!in_array($date, $tmp)) {
                        $line = array(
                            "jour" => $date,
                            "ferie" => 1,
                            "fermeture" => 0,
                            "nom" => jour_ferie($date),
                            "commentaire" => "Ajouté automatiquement"
                        );
                        $jours[]=$line;
                    }
                }
            }
            // Affichage des jours fériés enregistrés
            $i = 0;
            foreach ($jours as $elem) {
                if ($elem['ferie']){
                    $ferie = true;
                }else{
                    $ferie = false;
                }
                if ($elem['fermeture']){
                    $fermeture = true;
                }else{
                $fermeture = false;
                }
                $date = dateFr($elem['jour']);
                $commentaire = $elem['commentaire'];
                $nom = $elem['nom'];
                $days[] = array(
                    "holiday" => $ferie,
                    "closed"  => $fermeture,
                    "date"    => $date,
                     "comment" => $commentaire,
                    "name"    => $nom,
                    "number"  => $i
                );
                $i++;
            }
        }

        $nbDays = count($jours);
        $nbExtra = $nbDays + 15;

        $holiday_enable = $this->config('Conges-Enable');

        $this->templateParams(array(
            "CSRFSession"        => $GLOBALS['CSRFSession'],
            "days"               => $days,
            "holiday_enable"     => $holiday_enable,
            "nbDays"             => $nbDays,
            "nbExtra"            => $nbExtra,
            "selectedYear"       => $annee_select,
            "years"              => $annees
        ));

        return $this->output("closingdays/index.html.twig");

    }

    /**
     * @Route("/closingday", name="closingday.save", methods={"POST"})
     */
    public function save(Request $request, Session $session){
        $post = $request->request->all();
        $CSRFToken = $request->get('CSRFToken');

        $data=array();
        $keys=array_keys($post['jour']);
        foreach ($keys as $elem) {
            if ($post['jour'][$elem] and $post['jour'][$elem] != "0000-00-00") {
                $ferie = isset($post['ferie'][$elem]) ? 1 : 0;
                $fermeture = isset($post['fermeture'][$elem]) ? 1 : 0;
                $data[] = array("annee"=>$post['annee'],"jour"=>dateSQL($post['jour'][$elem]),"ferie"=>$ferie,"fermeture"=>$fermeture,"nom"=>$post['nom'][$elem],"commentaire"=>$post['commentaire'][$elem]);
            }
        }

        $db = $this->entityManager->createQueryBuilder();
        $db->delete(ClosingDay::class, 'cd')
           ->where('cd.annee = :annee')
           ->setParameter('annee', $post['annee']);
        $db->getQuery()->execute();

        $errors = array();
        if (!empty($data)) {
            foreach ($data as $elem){
                $cd = new ClosingDay;
                $cd->annee($elem['annee']);
                $cd->jour(\DateTime::createFromFormat('Y-m-d', $elem['jour']));
                $cd->ferie($elem['ferie']);
                $cd->fermeture($elem['fermeture']);
                $cd->nom($elem['nom']);
                $cd->commentaire($elem['commentaire']);
                try{
                    $this->entityManager->persist($cd);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $errors[] = $e->getMessage();
                }
            }
        }
 
        if (!empty($errors)){
            $session->getFlashBag()->add('error',"Une erreur est survenue lors de la modification de la liste des jours fériés.");
        } else {
            $session->getFlashBag()->add('notice',"La liste des jours fériés a été modifiée avec succès.");
        }

        return $this->redirectToRoute('closingday.index');
    }
}