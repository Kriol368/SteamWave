<?php

namespace App\Controller;

use App\Repository\PostRepository;
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
    public function query($input, PostRepository $postRepository): Response
    {
        $posts = $postRepository->findByContent($input);

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $bannerGameId = $user->getBanner();
        $banner = $bannerGameId ? $this->steamAppService->getGameBannerUrl($bannerGameId) : null;

        $postsWithImages = [];
        foreach ($posts as $post) {
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


        return $this->render('search/index.html.twig', [
            'posts' => $postsWithImages,
            //'user' => $user,
            //'banner' => $banner,
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
