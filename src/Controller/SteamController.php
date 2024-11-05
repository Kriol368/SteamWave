<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
////Esto es para testear
class SteamController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/user/steam-info', name: 'app_user_steam_info')]
    public function fetchSteamInfo(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user || !$user->getSteamID64()) {
            return new Response('No Steam ID associated with the logged-in user.', Response::HTTP_BAD_REQUEST);
        }

        // Replace with your actual Steam Web API key
        $steamApiKey = '562D15EBA45FEC235C627957E91296DE';
        $steamId = $user->getSteamID64();

        try {
            // Fetch basic profile data
            $playerSummary = $this->fetchSteamData("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/", [
                'key' => $steamApiKey,
                'steamids' => $steamId,
            ]);

            // Fetch owned games data
            $ownedGames = $this->fetchSteamData("https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/", [
                'key' => $steamApiKey,
                'steamid' => $steamId,
                'include_appinfo' => true,
                'include_played_free_games' => true,
            ]);

            // Fetch recently played games data
            $recentGames = $this->fetchSteamData("https://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/", [
                'key' => $steamApiKey,
                'steamid' => $steamId,
            ]);

            return $this->render('steam/index.html.twig', [
                'playerSummary' => $playerSummary['response']['players'][0] ?? null,
                'ownedGames' => $ownedGames['response']['games'] ?? [],
                'recentGames' => $recentGames['response']['games'] ?? [],
            ]);

        } catch (\Exception $e) {
            return new Response('Error fetching data from Steam: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function fetchSteamData(string $url, array $queryParams): ?array
    {
        $response = $this->httpClient->request('GET', $url, ['query' => $queryParams]);
        return $response->toArray();
    }
}