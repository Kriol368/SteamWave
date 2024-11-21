<?php

// src/Service/SteamAppService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamAppService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getUserGames(string $steamID64): array
    {
        $response = $this->client->request('GET', 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/', [
            'query' => [
                'key' => $this->apiKey,
                'steamid' => $steamID64,
                'include_appinfo' => true,      // Include game info like name and icon
                'include_played_free_games' => true,  // Include free games if available
                'format' => 'json',
            ],
        ]);

        $data = $response->toArray();

        $games = [];

        if (isset($data['response']['games'])) {
            foreach ($data['response']['games'] as $game) {
                $games[$game['appid']] = [
                    'name' => $game['name'] ?? 'Unknown Game',
                    'playtime_forever' => $game['playtime_forever'] ?? 0, // in minutes
                    'icon' => $this->getGameIconUrl($game['appid'], $game['img_icon_url'] ?? null),
                    'logo' => $this->getGameLogoUrl($game['appid'], $game['img_logo_url'] ?? null),
                ];
            }
        }

        return $games;
    }

    private function getGameIconUrl(int $appId, ?string $iconHash): ?string
    {
        if ($iconHash) {
            return "http://media.steampowered.com/steamcommunity/public/images/apps/{$appId}/{$iconHash}.jpg";
        }

        return null;
    }

    private function getGameLogoUrl(int $appId, ?string $logoHash): ?string
    {
        if ($logoHash) {
            return "http://media.steampowered.com/steamcommunity/public/images/apps/{$appId}/{$logoHash}.jpg";
        }

        return null;
    }

    public function getGameName(int $appId): ?string
    {
        $response = $this->client->request('GET', 'http://store.steampowered.com/api/appdetails', [
            'query' => [
                'appids' => $appId,
            ],
        ]);

        $data = $response->toArray();

        if (isset($data[$appId]['success']) && $data[$appId]['success']) {
            return $data[$appId]['data']['name'] ?? null;
        }

        return null;
    }

    public function getUserProfileImage(string $steamID64): ?string
    {
        $response = $this->client->request('GET', 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/', [
            'query' => [
                'key' => $this->apiKey,
                'steamids' => $steamID64,
            ],
        ]);

        $data = $response->toArray();

        if (isset($data['response']['players'][0]['avatarfull'])) {
            return $data['response']['players'][0]['avatarfull']; // Return the full-size avatar URL
        }

        return null; // Return null if no avatar is found
    }

    public function getGameDetails(int $appId): ?array
    {
        $response = $this->client->request('GET', 'https://store.steampowered.com/api/appdetails', [
            'query' => [
                'appids' => $appId,
            ],
        ]);

        $data = $response->toArray();

        if (isset($data[$appId]['success']) && $data[$appId]['success']) {
            return $data[$appId]['data'];
        }

        return null; // Return null if the game details cannot be fetched
    }


}
