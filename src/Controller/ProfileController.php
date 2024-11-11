<?php

namespace App\Controller;

use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProfileController extends AbstractController
{

    private SteamAppService $steamAppService;
    private Security $security;

    public function __construct(SteamAppService $steamAppService, Security $security)
    {
        $this->steamAppService = $steamAppService;
        $this->security = $security;
    }


    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
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
