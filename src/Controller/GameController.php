<?php
namespace App\Controller;

use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    private SteamAppService $steamAppService;

    public function __construct(SteamAppService $steamAppService)
    {
        $this->steamAppService = $steamAppService;
    }

    #[Route('/game/{appId}', name: 'app_game')]
    public function index(int $appId): Response
    {
        // Fetch detailed game information
        $gameDetails = $this->steamAppService->getGameDetails($appId);

        if (!$gameDetails) {
            throw $this->createNotFoundException("Game with ID $appId not found.");
        }

        return $this->render('game/index.html.twig', [
            'game' => $gameDetails,
        ]);
    }
}
