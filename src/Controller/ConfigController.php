<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Config;
use App\Planno\Helper\ConfigHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfigController extends BaseController
{
    #[Route(path: '/config/{options?}', name: 'config.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Temporary folder
        $tmp_dir=sys_get_temp_dir();

        $url = $this->entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'URL'])
            ->getValue();

        $technical = $request->attributes->get('options') == 'technical' ? 1 : 0;

        $configParams = $this->entityManager->getRepository(Config::class)->findBy(
            array('technical' => $technical),
            array('categorie' => 'ASC', 'ordre' => 'ASC', 'id' => 'ASC')
        );

        $elements = array();
        foreach ($configParams as $cp) {

            // Do not display hidden information
            if ($cp->getType() == 'hidden') {
                continue;
            }

            $elem = [
                'type'          => $cp->getType(),
                'nom'           => $cp->getName(),
                'valeur'        => $cp->getValue(),
                'valeurs'       => $cp->getValues(),
                'categorie'     => $cp->getCategory(),
                'commentaires'  => $cp->getComment(),
            ];

            if ($cp->getType() == 'password') {
                $elem['valeur'] = '';
            }
            switch ($elem['type']) {
                case "checkboxes":
                    $elem['valeurs'] = json_decode($elem['valeurs'], true);
                    $elem['choisies'] = json_decode($elem['valeur'], true);
                    break;
                // Select avec valeurs séparées par des virgules
                case "enum":
                    $options=explode(",", $elem['valeurs']);
                    $elem['options'] = $options;
                    break;
                // Select avec valeurs dans un tableau PHP à 2 dimensions
                case "enum2":
                    $elem['options'] = json_decode($elem['valeurs'], true);
                    break;
                case 'number':
                    $options_tab = json_decode($elem['valeurs'], true);
                    $options = [];
                    if (array_key_exists('min', $options_tab)) {
                        $options[] = 'min="' . (float) $options_tab['min'] . '"';
                    }
                    if (array_key_exists('max', $options_tab)) {
                        $options[] = 'max="' . (float) $options_tab['max'] . '"';
                    }
                    if (array_key_exists('step', $options_tab)) {
                        $options[] = 'step="' . (float) $options_tab['step'] . '"';
                    }
                    $options = implode(' ', $options);
                    $elem['options'] = $options;
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
            $elements[$category][$cp->getName()] = $elem;
        }

        $this->templateParams(array(
            'elements'  => $elements,
            'technical' => $technical
        ));

        return $this->output('config/index.html.twig');
    }

    #[Route(path: '/config', name: 'config.update', methods: ['POST'])]
    public function update(Request $request, Session $session): RedirectResponse
    {
        if (!$this->csrf_protection($request)) {
            $session->set('AccessDeniedReason', 'CSRF');
            return $this->redirectToRoute('access-denied');
        }

        $params = $request->request->all();

        // Demo mode
        if ($params !== [] && !empty($this->config('demo'))) {
            $error = "La modification de la configuration n'est pas autorisée sur la version de démonstration.";
            $error .= "#BR#Merci de votre compréhension";
        }
        elseif ($params !== []) {
            $configHelper = new ConfigHelper();
            $error = $configHelper->saveConfig($params);
        }

        $options = $params['technical'] ? ['options' => 'technical'] : [];

        if (!empty($error)) {
            $this->addFlash('error', $error);
        } else {
            $this->addFlash('notice', 'La configuration a été modifiée avec succès');
        }

        return $this->redirectToRoute('config.index', $options);
    }

    #[Route('/config/ldap-test', name: 'config.ldap_test', methods: ['POST'])]
    public function ldapTest(Request $request): JsonResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->json('CSRF');
        }

        $filter = $request->request->get('filter');
        $host = $request->request->get('host');
        $idAttribute = $request->request->get('idAttribute');
        $protocol = $request->request->get('protocol');
        $rdn = $request->request->get('rdn');
        $suffix = $request->request->get('suffix');
        $password = $request->request->get('password');
        $port = $request->request->getInt('port');

        if ($password == '') {
            $configRepository = $this->entityManager->getRepository(Config::class);
            $password = decrypt($configRepository->getValue('LDAP-Password'));
        }

        // Connexion au serveur LDAP
        $url = $protocol . '://' . $host . ':' . $port;

        $return = 'error';

        if ($fp = @fsockopen($host, $port, $errno, $errstr, 5)) {
            if ($ldapconn = ldap_connect($url)) {
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

                if ($bind = @ldap_bind($ldapconn, $rdn, $password)) {
                    $return = $search = @ldap_search($ldapconn, $suffix, $filter, array($idAttribute)) ? 'ok' : 'search';
                } else {
                    $return = 'bind';
                }
            }
        }
        return $this->json($return);
    }

    #[Route(path: '/config/mail-test', name: 'config.mailtest', methods: ['POST'])]
    public function mailTest(Request $request, TranslatorInterface $translator): JsonResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->json('CSRF');
        }

        $mailSmtp = $request->request->get('mailSmtp');
        $hostname = $request->request->get('hostname');
        $host = $request->request->get('host');
        $port = $request->request->getInt('port');
        $secure = $request->request->get('secure');
        $autoTLS = $request->request->get('autoTLS');
        $auth = $request->request->get('auth');
        $user = $request->request->get('user');
        $password = $request->request->get('password');
        $fromMail = $request->request->get('fromMail');
        $fromName = $request->request->get('fromName');
        $signature = $request->request->get('signature');
        $planning = $request->request->get('planning');

        if ($password == '') {
            $configRepository = $this->entityManager->getRepository(Config::class);
            $password = decrypt($configRepository->getValue('Mail-Password'));
        }

        // Connexion au serveur de messagerie
        if ($fp=@fsockopen($host, $port, $errno, $errstr, 5)) {
            $GLOBALS['config']['Mail-IsEnabled'] = 1;
            $GLOBALS['config']['Mail-IsMail-IsSMTP'] = $mailSmtp;
            $GLOBALS['config']['Mail-Hostname'] = $hostname;
            $GLOBALS['config']['Mail-Host'] = $host;
            $GLOBALS['config']['Mail-Port'] = $port;
            $GLOBALS['config']['Mail-SMTPSecure'] = $secure;
            $GLOBALS['config']['Mail-SMTPAutoTLS'] = $autoTLS;
            $GLOBALS['config']['Mail-SMTPAuth'] = $auth;
            $GLOBALS['config']['Mail-Username'] = $user;
            $GLOBALS['config']['Mail-Password'] = encrypt($password);
            $GLOBALS['config']['Mail-From'] = $fromMail;
            $GLOBALS['config']['Mail-FromName'] = $fromName;
            $GLOBALS['config']['Mail-Signature'] = $signature;
            $GLOBALS['config']['Mail-Planning'] = $planning;

            $m = new \CJMail();
            $m->subject = 'Message de test';
            $m->message = 'Message de test.<br/><br/>La messagerie de votre application Planno est correctement paramétrée.';
            $m->to = $planning;
            $m->send();

            if ($m->error) {
                return $this->json($m->error_CJInfo);
            } else {
                return $this->json('ok');
            }
        } else {
            return $this->json('socket');
        }
    }
}
