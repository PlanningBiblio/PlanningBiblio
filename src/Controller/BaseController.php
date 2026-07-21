<?php

namespace App\Controller;

use App\Entity\Config;
use App\Planno\Notifier;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class BaseController extends AbstractController
{
    protected $entityManager;
    private $templateParams = [];
    protected $dispatcher;
    protected $config = [];
    protected $logger;
    protected $notifier;
    protected $permissions;
    protected $request;
    protected $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RequestStack $requestStack,
        TranslatorInterface $translator
    )
    {
        $this->request = $requestStack->getCurrentRequest();

        /*
         * TODO FIXME
         * In some unit tests, we do not get the same result depending on whether we use $this->entityManager = $GLOBALS['entityManager']; or $this->entityManager = $entityManager;
         * E.g.: in tests/Controller/AdminInfoControllerTest.php:30
         * AdminInfoControllerTest::testAdd is successful with $GLOBALS['entityManager'], but fails with $entityManager.
         * With $GLOBALS['entityManager'], dates are returned with the format YYYYMMDD, with $entityMananger, the dates are returned with the format YYYY-MM-DD
         *
         * 1) AdminInfoControllerTest::testAdd is successful with $GLOBALS['entityManager'], but fails with $entityManager.
         * debut is 20211005
         * Failed asserting that two strings are equal.
         * --- Expected
         * +++ Actual
         * @@ @@
         * -'20211005'
         * +'2021-10-05'
         *
         * NB: It is possible that the tests are poorly written.
         */
        // $this->entityManager = $entityManager;
        $this->entityManager = $GLOBALS['entityManager'];

        $this->templateParams = $GLOBALS['templates_params'];

        $this->dispatcher = $GLOBALS['dispatcher'];

        $this->logger = $logger;

        $this->permissions = $GLOBALS['droits'];

        /*
         * TODO FIXME
         * Some unit tests fail if we do not use  $url and $GLOBLAS['config']
         * The result return by Config::getAll may be incomplete
         */
        // $this->config = $entityManager->getRepository(Config::class)->getAll();
        $url = $this->entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'URL'])
            ->getValue();

        $GLOBALS['config']['URL'] = $url;
        $this->config = $GLOBALS['config'];

        $this->translator = $translator;
    }

    public function setNotifier(Notifier $notifier): void {
        $this->notifier = $notifier;
    }

    protected function templateParams( array $params = array() )
    {
        if ( empty($params) ) {
            return $this->templateParams;
        }

        foreach ($params as $key => $value) {
            $this->templateParams[$key] = $value;
        }

        return $this;
    }

    protected function output($templateName)
    {
        return $this->render($templateName, $this->templateParams);
    }

    protected function config($key, $value = null)
    {
        if ( !isset($key) ) {
            return null;
        }

        if ( isset($value) ) {
            $this->config[$key] = $value;
            return null;
        }

        if ( !isset($this->config[$key]) ) {
            return null;
        }

        return $this->config[$key];
    }

    /**
     * Initialize and return a DateTime object using:
     * - the query parameter named $queryName, or
     * - the session parameter named $sessionName if the query parameter is absent or empty.
     *
     * If both the query parameter and the session parameter are absent, empty
     * or invalid, the DateTime object is initialized with the method parameter
     * $when, which can be any string accepted by DateTime constructor
     * (default: "today")
     *
     * The query parameter and the session parameter must be in the format
     * specified in $format (default: "d/m/Y")
     *
     * The resulting date is then stored in the session parameter named
     * $sessionName using the format specified in $format
     */
    protected function initDate(string $queryName, string $sessionName, string $when = 'today', string $format = 'd/m/Y'): DateTime
    {
        $session = $this->request->getSession();

        $date = $this->request->query->get($queryName) ?: $session->get($sessionName);

        $dt = $date ? DateTime::createFromFormat($format, $date) : null;
        if (!$dt) {
            $dt = new DateTime($when);
        }

        $session->set($sessionName, $dt->format($format));

        return $dt;
    }

    protected function csrf_protection(Request $request): bool
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            $error = $this->translator->trans(
                'The CSRF token is invalid. Please try to resubmit the form.',
                [],
                'validators'
            );

            $this->addFlash('error', $error);
            return false;
        }
        return true;
    }

    protected function returnError($error, $module = 'Planno', $status = 200): \Symfony\Component\HttpFoundation\Response
    {
        $this->logger->error($module . ':' . $error);
        $response = new Response();
        $response->setContent(json_encode(array('error' => $error)));
        $response->setStatusCode($status);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

}
