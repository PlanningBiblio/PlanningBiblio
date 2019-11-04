<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ConfigParam;
class ConfigController extends Controller
{
    /**
     * @Route("/config", name="config.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        // Temporary folder
        $tmp_dir=sys_get_temp_dir();
        // App URL
        $url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
        $entityManager = $GLOBALS['entityManager'];
        $configParams = $entityManager->getRepository(ConfigParam::class)->findBy(
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
        $templates_params['elements'] = $elements;
        $templates_params['error'] = $request->query->get('error');
        $templates_params['post'] = $request->query->get('post');
        $templates_params['warning'] = $request->query->get('warning');
        $templates_params = array_merge($templates_params, $GLOBALS['templates_params']);
        return $this->render('config/index.html.twig', $templates_params);
    }

    /**
     * @Route("/config", name="config.update"), methods={"POST"})
     */
    public function update(Request $request)
    {
        $params = $request->request->all();
        // Demo mode
        if ($params && !empty($GLOBALS['config']['demo'])) {
            $warning = "La modification de la configuration n'est pas autorisée sur la version de démonstration.";
            $warning .= "#BR#Merci de votre compréhension";
            $templates_params['warning'] = $warning;
        }
        elseif ($params && CSRFTokenOK($params['CSRFToken'], $_SESSION)) {
            $entityManager = $GLOBALS['entityManager'];
            $configParams = $entityManager->getRepository(ConfigParam::class)->findBy(
                array(), array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
            );
            $templates_params['post'] = 1;
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
                    $entityManager->persist($cp);
                }
                catch (Exception $e) {
                    $templates_params['error'] = true;
                }
            }
            $entityManager->flush();
        }
        return $this->redirectToRoute('config.index', $templates_params);
    }
}