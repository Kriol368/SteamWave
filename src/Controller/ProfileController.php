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
        // Render the template directly without any form or additional data
        return $this->render('profile/user_games.html.twig');
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
