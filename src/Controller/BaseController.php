<?php

namespace App\Controller;

use App\Entity\Config;
use App\PlanningBiblio\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class BaseController extends AbstractController
{
    protected $entityManager;

    private $templateParams = array();

    protected $dispatcher;

    protected $config = array();

    protected $logger;

    protected $notifier;

    protected $permissions;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, RequestStack $requestStack)
    {
        if (!empty($_ENV['MEMORY_LIMIT'])) {
            ini_set('memory_limit', $_ENV['MEMORY_LIMIT']);
        }

        $request = $requestStack->getCurrentRequest();

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
    }

    public function setNotifier(Notifier $notifier) {
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

    protected function csrf_protection(Request $request)
    {
        $submittedToken = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('', $submittedToken)) {
            $session = $request->getSession();
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return false;
        }
        return true;
    }

    protected function returnError($error, $module = 'Planno', $status = 200)
    {
        $this->logger->error($module . ':' . $error);
        $response = new Response();
        $response->setContent(json_encode(array('error' => $error)));
        $response->setStatusCode($status);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

}
