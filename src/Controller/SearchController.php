<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private SteamAppService $steamAppService;

    public function __construct(SteamAppService $steamAppService)
    {
        $this->steamAppService = $steamAppService;
    }

    #[Route('/search/{input}', name: 'app_search_query')]
    public function query($input, PostRepository $postRepo, UserRepository $userRepo): Response
    {
        $queryPosts = $postRepo->findByContent($input);
        $queryUsers = $userRepo->findByName($input);

        $loggedUser = $this->getUser();
        if (!$loggedUser) { throw $this->createAccessDeniedException();   }

        $bannerGameId = $loggedUser->getBanner();
        $banner = $bannerGameId ? $this->steamAppService->getGameBannerUrl($bannerGameId) : null;

        // ========0
        // este bloque organiza posts en un array para el partial
        $postsWithImages = [];
        foreach ($queryPosts as $post) {
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

        // ========12357890
        // este bloque organiza usuarios en un array para el partial
        $users = [];
        foreach ($queryUsers as $user) {
            // cogemos al user le joseamos el steam64 y de ahí encontramos la pfp
            $steamID64 = $user->getUser()->getSteamId64();
            $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

            $users[] = [
                'id'=>$post->getId(),
                'content' => $post->getContent(),
                'tag' => $gameName,
                'image' => $post->getImage(),
                'profilePicture' => $profileImage,
                'username' => $post->getPostUser()->getSteamUsername(),
            ];
        }

        return $this->render('search/index.html.twig', [
            'posts' => $postsWithImages,
            'user' => $loggedUser,
            //'banner' => $banner,
            'users' => $users,
        ]);
    }
    #[Route('/search', name: 'app_search')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }
}
