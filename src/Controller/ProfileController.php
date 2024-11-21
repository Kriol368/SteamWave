<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Security;

class ProfileController extends AbstractController
{

    private SteamAppService $steamAppService;
    private Security $security;
    private postRepository $postRepository;

    public function __construct(SteamAppService $steamAppService, Security $security, PostRepository $postRepository)
    {
        $this->steamAppService = $steamAppService;
        $this->security = $security;
        $this->postRepository = $postRepository;
    }


    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(UserRepository $userRepository, PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Debe estar autenticado para ver esta página.');
        }

        $posts = $postRepository->findBy(['postUser' => $user->getId()], ['publishedAt' => 'DESC']);


        $postsWithImages = [];
        foreach ($posts as $post) {
            $steamID64 = $post->getPostUser()->getSteamId64();
            $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

            // Fetch the game name using the post tag (Steam App ID)
            $gameName = $this->steamAppService->getGameName($post->getTag());

            $postsWithImages[] = [
                'id'=>$post->getId(),
                'content' => $post->getContent(),
                'tag' => $gameName,
                'image' => $post->getImage(),
                'profilePicture' => $profileImage,
                'username' => $post->getPostUser()->getSteamUsername(),
            ];
        }

        // En este caso, asumimos que $user ya es la entidad `User`
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $postsWithImages,
        ]);
    }

    #[Route('/user/games', name: 'user_games')]
    public function showUserGamesPage(): Response
    {
        $user = $this->security->getUser();

        if (!$user || !$user->getSteamID64()) {
            // Handle case where the user doesn't have a SteamID64
            return $this->render('profile/user_games.html.twig', [
                'games' => [],  // Return an empty list if the user doesn't have SteamID64
            ]);
        }

        // Fetch the user's games using SteamAppService
        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return $this->render('profile/user_games.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/user/games-list', name: 'user_games_list')]
    public function getUserGamesList(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse([], 400);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }
}
