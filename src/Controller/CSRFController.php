<?php

namespace App\Controller;

use App\PlanningBiblio\Notifier;

use App\Controller\BaseController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
//use Symfony\Component\HttpFoundation\RequestStack;
//use Psr\Log\LoggerInterface;

class CSRFController extends AbstractController
{
  /*  public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $request = $requestStack->getCurrentRequest();
        $this->logger = $logger;
    }*/
    protected $notifier;

    /** @var Symfony\Component\Security\Csrf\CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager) 
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function csrf_protection($token)
    {
    //    $token = $this->request('_token');
        error_log("test : $token");

        if (empty($token)) {
            die("The CSRF token is absent !");
        }

        if ( ! $this->csrfTokenManager->isTokenValid(
            new CsrfToken('', $token)) ) {
            die("The CSRF token is not valid !");
        }
        //if (!$this->isCsrfTokenValid('', $submittedToken)) {
        //    die("The CSRF token is not valid !");
        //}
    }

    public function setNotifier(Notifier $notifier) {
        $this->notifier = $notifier;
    }

}

?>
