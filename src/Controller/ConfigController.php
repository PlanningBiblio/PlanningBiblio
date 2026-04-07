<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Config;
use App\Entity\NetworkConfig;
use App\Entity\TechnicalConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends BaseController
{
    #[Route(path: '/config/{options?}', name: 'config.index', methods: ['GET'])]
    public function index(Request $request)
    {
        // Temporary folder
        $tmp_dir=sys_get_temp_dir();

        $url = $this->configFinder->findOneByConfigName(TechnicalConfig::class, 'URL')
            ->getValue();

        $technical = $request->get('options') == 'technical' ? 1 : 0;

        $entityClass = $technical ? TechnicalConfig::class : NetworkConfig::class;
        $networkId = $technical ? null : $_SESSION['network']['id'];
        $configParams = $this->configFinder->findByType($entityClass, $networkId);

        $elements = array();
        foreach ($configParams as $cp) {

            // Do not display hidden information
            if ($cp->getConfig()->getType() == 'hidden') {
                continue;
            }

            $elem = array(
                'type'          => $cp->getConfig()->getType(),
                'nom'           => $cp->getConfig()->getName(),
                'valeur'        => html_entity_decode($cp->getValue(), ENT_QUOTES|ENT_HTML5),
                'valeurs'       => html_entity_decode($cp->getConfig()->getValues(), ENT_QUOTES|ENT_HTML5),
                'categorie'     => $cp->getConfig()->getCategory(),
                'commentaires'  => html_entity_decode($cp->getConfig()->getComment(), ENT_QUOTES|ENT_HTML5),
            );

            if ($cp->getConfig()->getType() == 'password') {
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
            $elements[$category][$cp->getConfig()->getName()] = $elem;
        }

        $this->templateParams(array(
            'elements'  => $elements,
            'error'     => $request->query->get('error'),
            'post'      => $request->query->get('post'),
            'technical' => $technical,
            'warning'   => $request->query->get('warning')
        ));

        return $this->output('config/index.html.twig');
    }

    #[Route(path: '/config', name: 'config.update', methods: ['POST'])]
    public function update(Request $request, Session $session): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $params = $request->request->all();

        // Demo mode
        if ($params !== [] && !empty($this->config('demo'))) {
            $error = "La modification de la configuration n'est pas autorisée sur la version de démonstration.";
            $error .= "#BR#Merci de votre compréhension";
        }
        elseif ($params !== []) {

            $technical = $request->get('technical');

            $entityClass = $technical ? TechnicalConfig::class : NetworkConfig::class;
            $networkId = $technical ? null : $_SESSION['network']['id'];
            $configParams = $this->configFinder->findByType($entityClass, $networkId);

            foreach ($configParams as $cp) {
                if (in_array($cp->getConfig()->getType(), ['hidden', 'info'])) {
                    continue;
                }
                // boolean and checkboxes elements.
                if (!isset($params[$cp->getConfig()->getName()])) {
                    $params[$cp->getConfig()->getName()] = $cp->getConfig()->getType() == 'boolean' ? '0' : array();
                }
                $value = $params[$cp->getConfig()->getName()];

                if (is_string($value)) {
                    $value = trim($value);
                }

                // Passwords
                if (substr($cp->getConfig()->getName(), -9) == '-Password') {
                    $value = encrypt($value);
                }
                // Checkboxes
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if ($cp->getConfig()->getType() == 'color') {
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

        }

        if (isset($error)) {
            $session->getFlashBag()->add('error', $error);
        } else {
            $flash = 'La configuration a été modifiée avec succès';
            $session->getFlashBag()->add('notice', $flash);
        }

        $options = $technical ? ['options' => 'technical'] : [];

        return $this->redirectToRoute('config.index', $options);
    }

    #[Route('/config/ldap-test', name: 'config.ldap_test', methods: ['POST'])]
    public function ldapTest(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $filter = $request->get('filter');
        $host = $request->get('host');
        $idAttribute = $request->get('idAttribute');
        $protocol = $request->get('protocol');
        $rdn = $request->get('rdn');
        $suffix = $request->get('suffix');
        $password = $request->get('password');
        $port = $request->get('port');

        $port = filter_var($port, FILTER_SANITIZE_NUMBER_INT);

        // Connexion au serveur LDAP
        $url = $protocol . '://' . $host . ':' . $port;

        $return = ['error'];

        if ($fp = @fsockopen($host, $port, $errno, $errstr, 5)) {
            if ($ldapconn = ldap_connect($url)) {
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

                if ($bind = @ldap_bind($ldapconn, $rdn, $password)) {
                    $return = $search = @ldap_search($ldapconn, $suffix, $filter, array($idAttribute)) ? ['ok'] : ['search'];
                } else {
                    $return = ['bind'];
                }
            }
        }

        return new Response(json_encode($return));
    }
}
