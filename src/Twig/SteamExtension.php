<?php

namespace App\Twig;

use App\Service\SteamAppService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SteamExtension extends AbstractExtension
{
    private SteamAppService $steamAppService;

    public function __construct(SteamAppService $steamAppService)
    {
        $this->steamAppService = $steamAppService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('steam_profile_image', [$this, 'getSteamProfileImage']),
        ];
    }

    public function getSteamProfileImage(?string $steamID64): ?string
    {
        if ($steamID64) {
            return $this->steamAppService->getUserProfileImage($steamID64);
        }

        return null;
    }
}
