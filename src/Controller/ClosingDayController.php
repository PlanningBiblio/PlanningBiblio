<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__. '/../../public/joursFeries/class.joursFeries.php');

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

        $j = new \joursFeries();
        $j->fetchYears();
        $annees = $j->elements;

        if (!in_array($annee_suivante, $annees)) {
            $annees[] = $annee_suivante;
        }
        if (!in_array($annee_courante, $annees)) {
            $annees[] = $annee_courante;
        }

        sort($annees);

        // Recherche des jours fériés enregistrés dans la base de données et avec la fonction jour_ferie
        $j = new \joursFeries();
        $j->annee = $annee_select;
        $j->auto = false;;
        $j->fetch();
        $jours = $j->elements;

        $nbDays = count($jours);
        $nbExtra = $nbDays + 15;
        $days = [];
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

        $j = new \joursFeries();
        $j->CSRFToken = $CSRFToken;
        $j->update($post);

        if ($j->error){
            $session->getFlashBag()->add('error',"Une erreur est survenue lors de la modification de la liste des jours fériés.");
        } else {
            $session->getFlashBag()->add('notice',"La liste des jours fériés a été modifiée avec succès.");
        }

        return $this->redirectToRoute('closingday.index');
    }
}