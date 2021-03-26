<?php
namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Model\ConfigParam;

class ConfigController extends BaseController
{
    /**
     * @Route("/config", name="config.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        // Temporary folder
        $tmp_dir=sys_get_temp_dir();

        // App URL
        $request::setTrustedProxies(array($request->server->get('REMOTE_ADDR')), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
        $url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();

        $configParams = $this->entityManager->getRepository(ConfigParam::class)->findBy(
            array(),
            array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
        );
        $elements = array();
        foreach ($configParams as $cp) {
            $elem = array(
                'type'          => $cp->type(),
                'nom'           => $cp->nom(),
                'valeur'        => html_entity_decode($cp->valeur(), ENT_QUOTES|ENT_HTML5),
                'valeurs'       => html_entity_decode($cp->valeurs(), ENT_QUOTES|ENT_HTML5),
                'categorie'     => $cp->categorie(),
                'commentaires'  => html_entity_decode($cp->commentaires(), ENT_QUOTES|ENT_HTML5),
            );

            if ($elem['nom'] == 'URL' ) {
                $elem['valeur'] = $url;
            }
            if ($cp->type() == "password") {
                $elem['valeur']=decrypt($elem['valeur']);
            }
            switch ($elem['type']) {
                case "checkboxes":
                    $elem['valeurs'] = json_decode($elem['valeurs'], true);
                    $elem['choisies'] = json_decode($elem['valeur'], true);
                    break;
                // Select avec valeurs séparées par des virgules
                case "enum":
                    $options=explode(",", $elem['valeurs']);
                    $selected = null;
                    foreach ($options as $option) {
                        $selected = $option == htmlentities($elem['valeur'], ENT_QUOTES|ENT_IGNORE, "UTF-8", false) ? $elem['valeur'] : $selected;
                    }
                    $elem['valeur'] = $selected;
                    $elem['options'] = $options;
                    break;
                // Select avec valeurs dans un tableau PHP à 2 dimensions
                case "enum2":
                    $elem['options'] = json_decode(str_replace("&#34;", '"', $elem['valeurs']), true);
                    break;
                case "textarea":
                    $elem['valeur'] = str_replace("<br/>", "\n", $elem['valeur']);
                    break;
                case "date":
                    $elem['valeur'] = dateFr3($elem['valeur']);
                    break;
                default:
                    break;
            }
            $elem['commentaires'] = str_replace("[TEMP]", $tmp_dir, $elem['commentaires']);
            $elem['commentaires'] = str_replace("[SERVER]", $url, $elem['commentaires']);
            $category = str_replace('_', '', $elem['categorie']);
            $elements[$category][$cp->nom()] = $elem;
        }

        $this->templateParams(array(
            'elements'  => $elements,
            'error'     => $request->query->get('error'),
            'post'      => $request->query->get('post'),
            'warning'   => $request->query->get('warning')
        ));


        return $this->output('config/index.html.twig');
    }

    /**
     * @Route("/config", name="config.update"), methods={"POST"})
     */
    public function update(Request $request, Session $session)
    {
        $params = $request->request->all();
        // Demo mode
        if ($params && !empty($this->config('demo'))) {
            $error = "La modification de la configuration n'est pas autorisée sur la version de démonstration.";
            $error .= "#BR#Merci de votre compréhension";
        }
        elseif ($params && CSRFTokenOK($params['CSRFToken'], $_SESSION)) {

            $configParams = $this->entityManager->getRepository(ConfigParam::class)->findBy(
                array(), array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
            );

            foreach ($configParams as $cp) {
                if ($cp->type() == 'info' and $cp->nom() != 'URL') {
                    continue;
                }
                // boolean and checkboxes elements.
                if (!isset($params[$cp->nom()])) {
                    if ($cp->type() == 'boolean') {
                        $params[$cp->nom()] = '0';
                    } else {
                        $params[$cp->nom()] = array();
                    }
                }
                $value = $params[$cp->nom()];

                if (is_string($value)) {
                    $value = trim($value);
                }

                // App URL
                if ($cp->nom() == 'URL') {
                    $request::setTrustedProxies(array($request->server->get('REMOTE_ADDR')));
                    $value = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
                }
                if (substr($cp->nom(), -9)=="-Password") {
                    $value = encrypt($value);
                }
                // Checkboxes
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                try {
                    $cp->valeur($value);
                    $this->entityManager->persist($cp);
                }
                catch (Exception $e) {
                    $error = 'Une erreur est survenue pendant la modification de la configuration !';
                }
            }
            $this->entityManager->flush();

        }

        if (isset($error)) {
            $session->getFlashBag()->add('error', $error);
        } else {
            $flash = 'La configuration a été modifiée avec succès';
            $session->getFlashBag()->add('notice', $flash);
        }

        return $this->redirectToRoute('config.index');
    }
}