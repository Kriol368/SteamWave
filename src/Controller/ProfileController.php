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
    private UserRepository $userRepository;

    public function __construct(SteamAppService $steamAppService, Security $security, PostRepository $postRepository,userRepository $userRepository)
    {
        $this->steamAppService = $steamAppService;
        $this->security = $security;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }


    #[Route('/user/{id}/games-list', name: 'user_specific_games_list', methods: ['GET'])]
    public function getUserGamesList(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id); // Ensure UserRepository is injected and used here.

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse([], 400);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }


    #[Route('/user/games-list', name: 'user_logged_games_list')]
    public function geLoggedtUserGamesList(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse([], 400);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }

    #[Route('/profile/{userId}', name: 'view_profile', defaults: ['userId' => null])]
    public function viewProfile(
        ?int $userId,
        UserRepository $userRepository,
        PostRepository $postRepository
    ): Response {
        $user = $userId ? $userRepository->find($userId) : $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        $posts = $postRepository->findBy(['postUser' => $user->getId()], ['publishedAt' => 'DESC']);

        $postsWithImages = [];
        foreach ($posts as $post) {
            $steamID64 = $post->getPostUser()->getSteamId64();
            $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

            // Fetch the game name using the post tag (Steam App ID)
            $gameName = $this->steamAppService->getGameName($post->getTag());

            $postsWithImages[] = [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'tag' => $gameName,
                'image' => $post->getImage(),
                'profilePicture' => $profileImage,
                'username' => $post->getPostUser()->getSteamUsername(),
            ];
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $postsWithImages,
            'isOwnProfile' => $user === $this->getUser(),
        ]);
    }

}
