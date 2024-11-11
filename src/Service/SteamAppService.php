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
                'include_appinfo' => true,
                'format' => 'json',
            ],
        ]);

        $data = $response->toArray();

        if (isset($data['response']['games'])) {
            return array_column($data['response']['games'], 'name', 'appid'); // App ID as key, Name as value
        }

        return [];
    }
}
