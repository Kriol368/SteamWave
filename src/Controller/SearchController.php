<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\CloudinaryService;
use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function query(PostRepository $postRepo, UserRepository $userRepo, Request $request, CloudinaryService $cloudinaryService): Response
    {
        $input = $request->query->get('searchQueryInput', '');

        // ===============
        //  esta condicional comprueba si hay query, si no hay
        //  o está vacía te manda pal index otra vez.

        if($input == "q?searchQueryInput=" || $input == null){
            return $this->index();
        }

        else {
            $queryPosts = $postRepo->findByContent($input);
            $queryUsers = $userRepo->findByName($input);

            $loggedUser = $this->getUser();
            if (!$loggedUser) { throw $this->createAccessDeniedException();}

            // ========0
            // este bloque organiza posts en un array para el partial

            $posts = [];
            foreach ($queryPosts as $post) {
                $steamID64 = $post->getPostUser()->getSteamId64();
                $profileImage = $this->steamAppService->getUserProfileImage($steamID64);


                $posts[] = [
                    'id' => $post->getId(),
                    'content' => $post->getContent(),
                    'image' => $post->getImage(),
                    'profilePicture' => $profileImage,
                    'username' => $post->getPostUser()->getSteamUsername(),
                    'userId' => $post->getPostUser()->getId(),
                    'gameName' => $post->getGamename(),
                ];
            }

            // ========12357890
            // este bloque organiza usuarios en un array para el partial

            $users = [];
            foreach ($queryUsers as $user) {
                // cogemos al user le joseamos el steam64 y de ahí encontramos la pfp
                $steamID64 = $user->getSteamId64();
                $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

                $users[] = [
                    'userId' => $user->getId(),
                    'profilePicture' => $profileImage,
                    'username' => $user->getSteamUsername(),
                ];
            }

            return $this->render('search/index.html.twig', [
                'posts' => $posts,
                'user' => $loggedUser,
                //'banner' => $banner,
                'users' => $users,
                'cloudinaryService' => $cloudinaryService,
            ]);
        }
    }

    #[Route('/search', name: 'app_search')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }
}
