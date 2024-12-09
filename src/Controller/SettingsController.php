<?php

namespace App\Controller;

use App\Form\BannerFormType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\SteamAppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SettingsController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $postRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, PostRepository $postRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
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


    #[Route('/settings/banner', name: 'edit_banner', methods: ['GET', 'POST'])]
    public function editBanner(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }


        // Create and handle the form for selecting the game
        $form = $this->createForm(BannerFormType::class, null, [
            'method' => 'POST',
        ]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $banner = $form->get('banner')->getData();
            if ($banner) {
                $user->setBanner($banner);
            }


            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your banner has been updated successfully!');

            $this->redirectToRoute('app_settings');
        }

        return $this->render('settings/edit_banner.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/settings/delete-account', name: 'delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request, Security $security, PostRepository $postRepository,SessionInterface $session): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // CSRF protection check (optional but recommended)
        if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], 400);
        }

        // Get user's comments and posts, then delete them
        $comments = $user->getComments();
        $userId = [$user->getId()];
        $posts =  $postRepository->findPostsByUsers($userId);

        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
        }

        foreach ($posts as $post) {
            $this->entityManager->remove($post);
        }

        // Remove the user account
        // Remove the user account
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // Invalidate the session and log out the user
        $this->container->get('security.token_storage')->setToken(null);
        $session->invalidate();

        $this->addFlash('success', 'Your account has been deleted successfully.');

        // Redirect to the logout route to complete the process
        return $this->redirectToRoute('app_logout');
    }

}
