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


        return $this->render('home/index.html.twig', [
            'posts' => $postsWithImages,
            'user' => $user,
            'banner' => $banner,
        ]);
    }
}
