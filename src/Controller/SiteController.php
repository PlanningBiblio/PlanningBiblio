<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Network;
use App\Entity\Site;
use App\Entity\SiteMail;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends BaseController
{
    #[Route('/site', name: 'site.index', methods: ['GET'])]
    public function index(): Response
    {
        $sites = $this->entityManager->getRepository(Site::class)->findBy(['deletedDate' => null, 'network' => $_SESSION['network']['id']], ['name' => 'ASC']
        );

        $sitesTab = [];

        foreach ($sites as $site) {
            $mails = $this->entityManager->getRepository(SiteMail::class)->findBy(['site' => $site->getId()]);

            $mailsValues = array_map(fn($m) => $m->getMail(), $mails);
            $mailsAffiches = array_slice($mailsValues, 0, 3);

            $sitesTab[] = [
                'id' => $site->getId(),
                'name' => $site->getName(),
                'mails' => implode('; ', $mailsValues),
                'mailsAffiches' => implode('; ', $mailsAffiches) . (count($mailsValues) > 3 ? ' ...' : ''),
            ];
        }

        $this->templateParams(['sites' => $sitesTab,]);
        return $this->output('site/index.html.twig');
    }

    #[Route('/site/add', name: 'site.add', methods: ['GET'])]
    public function add(): Response
    {
        $this->templateParams([
            'id' => null,
            'site_name' => null,
            'mails' => [],
        ]);

        return $this->output('site/edit.html.twig');
    }

    #[Route('/site/{id<\d+>}', name: 'site.edit', methods: ['GET'])]
    public function edit(int $id): Response
    {
        $site = $this->entityManager->getRepository(Site::class)->find($id);

        if (!$site) {
            throw $this->createNotFoundException("Site introuvable");
        }

        $mails = array_map(
            fn($m) => $m->getMail(),
            $this->entityManager->getRepository(SiteMail::class)->findBy(['site' => $id])
        );

        $this->templateParams([
            'id' => $id,
            'site_name' => $site->getName(),
            'mails' => $mails,
        ]);

        return $this->output('site/edit.html.twig');
    }

    #[Route('/site', name: 'site.save', methods: ['POST'])]
    public function save(Request $request, Session $session): RedirectResponse
    {
        $id = $request->request->get('id');
        $name = trim($request->request->get('name', ''));

        $mails = [];
        $i = 1;
        while (($mail = $request->request->get("mail_$i")) !== null) {
            $mail = trim($mail);
            if ($mail !== '') {
                $mails[] = $mail;
            }
            $i++;
        }

        if ($name === '') {
            $session->getFlashBag()->add('error', "Le name du site ne peut pas être vide");
            return $this->redirectToRoute($id ? 'site.edit' : 'site.add', $id ? ['id' => $id] : []);
        }

        try {
            if (!$id) {
                $network = $this->entityManager->getRepository(Network::class)->find($_SESSION['network']['id']);

                $site = new Site();
                $site->setName($name);
                $site->setNetwork($network);

                $this->entityManager->persist($site);
                $this->entityManager->flush();

                $this->saveMails($site, $mails);

                $session->getFlashBag()->add('notice', "Le site a été ajouté avec succès");
            } else {
                $site = $this->entityManager->getRepository(Site::class)->find($id);

                if (!$site) {
                    throw new \RuntimeException("Site introuvable");
                }

                $site->setName($name);

                $this->entityManager->persist($site);
                $this->entityManager->flush();

                $this->saveMails($site, $mails);

                $session->getFlashBag()->add('notice', "Le site a été modifié avec succès");
            }
        } catch (Exception $e) {
            $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'enregistrement du site");
            $this->logger->error($e->getMessage());
        }

        return $this->redirectToRoute('site.index');
    }

    #[Route('/site/{id<\d+>}', name: 'site.delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $site = $this->entityManager->getRepository(Site::class)->find($id);

        if (!$site) {
            return $this->json("Site introuvable", Response::HTTP_NOT_FOUND);
        }

        if ($site->getId() == 1) {
            return $this->json("Impossible de supprimer le site par défaut", Response::HTTP_FORBIDDEN);
        }

        $agents_of_site = $this->entityManager->getRepository(Agent::class)->getAgentsForSite($site->getId());
        if (count($agents_of_site) > 0) {
            return $this->json("Impossible de supprimer le site car il est associé à des agents : "
                . implode(', ', array_map(fn($a) => $a->getLastname() . ' ' . $a->getFirstname(), $agents_of_site)),
                Response::HTTP_BAD_REQUEST);
        }

        try {
            $site->setDeletedDate(new \DateTime());
            $this->entityManager->persist($site);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $this->json("Erreur lors de la suppression", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json("Ok");
    }

    private function saveMails(Site $site, array $mails): void
    {
        $existing = $this->entityManager->getRepository(SiteMail::class)->findBy(['site' => $site]);
        foreach ($existing as $m) {
            $this->entityManager->remove($m);
        }
        $this->entityManager->flush();

        foreach ($mails as $mail) {
            $siteMail = new SiteMail();
            $siteMail->setSite($site);
            $siteMail->setMail($mail);
            $this->entityManager->persist($siteMail);
        }
        $this->entityManager->flush();
    }
}
