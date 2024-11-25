<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    private SteamAppService $steamAppService;
    private Security $security;
    private PostRepository $postRepository;
    private UserRepository $userRepository;

    public function __construct(
        SteamAppService $steamAppService,
        Security $security,
        PostRepository $postRepository,
        UserRepository $userRepository
    ) {
        $this->steamAppService = $steamAppService;
        $this->security = $security;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/user/{id}/games-list', name: 'user_specific_games_list', methods: ['GET'])]
    public function getUserGamesList(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse(['error' => 'Invalid user or SteamID64 not found'], Response::HTTP_BAD_REQUEST);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }

    #[Route('/user/games-list', name: 'user_logged_games_list', methods: ['GET'])]
    public function getLoggedUserGamesList(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse(['error' => 'User not authenticated or SteamID64 not found'], Response::HTTP_BAD_REQUEST);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }

    #[Route('/profile/{userId}', name: 'view_profile', defaults: ['userId' => null])]
    public function viewProfile(?int $userId): Response
    {
        $user = $userId ? $this->userRepository->find($userId) : $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // Fetch posts by user
        $posts = $this->postRepository->findBy(['postUser' => $user->getId()], ['publishedAt' => 'DESC']);

        // Prepare posts with additional data
        $postsWithImages = array_map(function ($post) {
            $steamID64 = $post->getPostUser()->getSteamId64();
            return [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'tag' => $this->steamAppService->getGameName($post->getTag()),
                'image' => $post->getImage(),
                'profilePicture' => $this->steamAppService->getUserProfileImage($steamID64),
                'username' => $post->getPostUser()->getSteamUsername(),
            ];
        }, $posts);

        // Fetch the user's banner game ID and retrieve the banner URL
        $bannerGameId = $user->getBanner();
        $banner = $bannerGameId ? $this->steamAppService->getGameBannerUrl($bannerGameId) : null;

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $postsWithImages,
            'isOwnProfile' => $user === $this->getUser(),
            'banner' => $banner,
        ]);
    }
}
