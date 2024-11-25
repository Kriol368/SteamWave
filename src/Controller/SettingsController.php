<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\SteamAppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/settings', name: 'app_settings')]
    public function index(): Response
    {
        return $this->render('settings/index.html.twig');
    }

    #[Route('/settings/description', name: 'edit_description', methods: ['GET', 'POST'])]
    public function editDescription(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');

            if (strlen($description) > 161) {
                return $this->json(['error' => 'Description cannot exceed 161 characters.'], 400);
            }

            $user->setDescription($description);

            // Persist and flush changes using EntityManager
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_settings'); // Redirect back to the main settings page
        }

        // Handle GET request - show the form
        return $this->render('settings/edit_description.html.twig');
    }


    #[Route('/settings/banner', name: 'edit_banner')]
    public function editBanner(): Response
    {
        return $this->render('settings/edit_banner.html.twig');
    }
}
