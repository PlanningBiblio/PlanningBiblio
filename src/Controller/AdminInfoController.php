<?php

namespace App\Controller;

use App\Controller\BaseController;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\AdminInfo;

class AdminInfoController extends BaseController
{
    #[Route(path: '/admin/info/{reset?}', name: 'admin.info.index', methods: ['GET'], requirements: ['reset' => 'reset'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $start = $this->initDate('start', 'AdminInfoStart');
        $end = $this->initDate('end', 'AdminInfoEnd', '+1 year');

        $info = $em->getRepository(AdminInfo::class)->findByDateRange($start, $end);

        $this->templateParams([
            'info' => $info,
            'start' => $start,
            'end' => $end,
            'title' => 'Informational messages',
        ]);

        return $this->output('adminInfo/index.html.twig');
    }

    #[Route(path: '/admin/info/add', name: 'admin.info.add', methods: ['GET'])]
    #[Route(path: '/admin/info/{id<\d+>}', name: 'admin.info.edit', methods: ['GET'])]
    public function edit(Request $request): Response | RedirectResponse
    {
        $id = $request->attributes->get('id');

        if ($id) {
            $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
            if (!$info) {
                $this->addFlash('error', $this->translator->trans('The requested information does not exist.'));
                return $this->redirectToRoute('admin.info.index');
            }
            $formTitle = 'Modify information';
        } else {
            $info = new AdminInfo();
            $formTitle = 'Add a piece of information';
        }

        $this->templateParams([
            'id'    => $id,
            'info'  => $info,
            'sitesSelect' => $this->sitesSelect($info),
            'title' => 'Informational messages',
            'formTitle' => $formTitle,
        ]);

        return $this->output('adminInfo/edit.html.twig');
    }

    #[Route(path: '/admin/info', name: 'admin.info.update', methods: ['POST'])]
    public function update(Request $request): RedirectResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->request->get('id');
        $start = DateTimeImmutable::createFromFormat('!d/m/Y', $request->request->getString('start'));
        $end = DateTimeImmutable::createFromFormat('!d/m/Y', $request->request->getString('end'));

        if (!$start) {
            $this->addFlash('error', $this->translator->trans('Please enter a valid start date'));

            return $id
                ? $this->redirectToRoute('admin.info.edit', ['id' => $id])
                : $this->redirectToRoute('admin.info.add');
        }
        $text = trim($request->get('text'));

        $htmlSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())->allowSafeElements()
        );

        $text = $htmlSanitizer->sanitize($text);
        $text = html_entity_decode($text);

        if (empty($end)) {
          $end = $start;
        }

        // Sites: an empty array means "all sites"
        $sites = [];
        $availableSites = array_column($this->sitesSelect(), 'id');

        if (!empty($availableSites)) {
            $sites = array_values(array_intersect(
                $availableSites,
                array_map('intval', $request->request->all('sites'))
            ));

            if (empty($sites)) {
                $this->addFlash('error', $this->translator->trans('Please select at least one site.'));

                return $id
                    ? $this->redirectToRoute('admin.info.edit', ['id' => $id])
                    : $this->redirectToRoute('admin.info.add');
            }

            // All sites checked: the message will also be displayed on sites created later
            if (count($sites) == count($availableSites)) {
                $sites = [];
            }
        }

        if ($id) {
            $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
            $info->setStart($start);
            $info->setEnd($end);
            $info->setText($text);
            $info->setSites($sites);
            $flash = $this->translator->trans('The information has been successfully modified.');
        } else {
            // Store a new info
            $info = new AdminInfo();
            $info->setStart($start);
            $info->setEnd($end);
            $info->setText($text);
            $info->setSites($sites);
            $flash = $this->translator->trans('The information has been successfully saved.');
        }

        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $this->addFlash('notice', $flash);
        return $this->redirectToRoute('admin.info.index');
    }

    #[Route(path: '/admin/info/delete', name: 'admin.info.delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        if (!$this->csrf_protection($request)) {
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent(json_encode('CSRF error'));

            return $response;
        }

        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
        $this->entityManager->remove($info);
        $this->entityManager->flush();

        $this->addFlash('notice', $this->translator->trans('The information has been successfully deleted.'));

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode('OK'));

        return $response;
    }

    /**
     * Returns the sites that can be selected in the form (multi-sites only).
     * Without $info (new message), all sites are checked.
     * A message for all sites (empty array) has all its sites checked.
     *
     * @return array<int, array{id: int, name: string, checked: bool}>
     */
    private function sitesSelect(?AdminInfo $info = null): array
    {
        $sitesSelect = [];

        if ($this->config('Multisites-nombre') < 2) {
            return $sitesSelect;
        }

        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {

            // Avoid displaying unamed or deleted sites
            if (empty($this->config('Multisites-site' . $i))) {
                continue;
            }

            $sitesSelect[] = [
                'id' => $i,
                'name' => $this->config('Multisites-site' . $i),
                'checked' => $info === null || $info->isDisplayedOnSite($i),
            ];
        }

        return $sitesSelect;
    }
}
