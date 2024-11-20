<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private SteamAppService $steamAppService;

    public function __construct(SteamAppService $steamAppService)
    {
        $this->steamAppService = $steamAppService;
    }



    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['publishedAt' => 'DESC']);


        $postsWithImages = [];
        foreach ($posts as $post) {
            $steamID64 = $post->getPostUser()->getSteamId64();
            $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

            $postsWithImages[] = [
                'content' => $post->getContent(),
                'tag' => $post->getTag(),
                'image' => $post->getImage(),
                'profilePicture' => $profileImage,
                'username' => $post->getPostUser()->getSteamUsername(),
            ];
        }


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'posts' => $postsWithImages,
        ]);
    }
}
