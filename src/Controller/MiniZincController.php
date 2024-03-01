<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;


class MiniZincController extends BaseController
{
    /**
     * @Route("/minizinc/oneday", name="minizinc.oneday", methods={"POST"})
     */
    public function oneDay(KernelInterface $kernel, Request $request): Response
    {

        if (!$this->csrf_protection($request)) {
            return new Response(json_encode(['error' => 'CSRF Token Error']));
        }

        $session = $request->getSession();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'MiniZinc:OneDay',
            'date' => $session->get('date'),
            'login' => $session->get('loginId'),
            'site' => $session->get('site'),
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = nl2br($output->fetch());

        $result = explode('###RESULT###', $content);

        if (!empty($result[1])) {
            return new Response(json_encode($result[1]));
        }

        return new Response('{}');
    }

    /**
     * @Route("/minizinc/tryme", name="minizinc.tryme", methods={"GET"})
     */
    public function tryMe(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'miniZinc:TryMe',
            '--a' => true,
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = nl2br($output->fetch());

        return new Response($content);
    }
}
