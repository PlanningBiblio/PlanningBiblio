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
     * @Route("/minizinc/oneday", name="minizinc.oneday", methods={"GET"})
     */
    public function oneDay(KernelInterface $kernel, Request $request): Response
    {

        $session = $request->getSession();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'MiniZinc:OneDay',
            'date' => $session->get('date'),
            'site' => $session->get('site'),
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = nl2br($output->fetch());

        return new Response($content);
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
