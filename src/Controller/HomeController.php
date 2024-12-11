<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\CloudinaryService;
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
    public function index(PostRepository $postRepository, CloudinaryService $cloudinaryService): Response
    {
        $posts = $postRepository->findBy([], ['publishedAt' => 'DESC']);
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirect to login if not authenticated
        }

        // Fetch banner
        $bannerGameId = $user->getBanner();
        $banner = $bannerGameId ? $this->steamAppService->getGameBannerUrl($bannerGameId) : null;

        // Process user's posts
        $postsWithImages = [];
        foreach ($posts as $post) {
            $steamID64 = $post->getPostUser()->getSteamId64();
            $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

            $postsWithImages[] = [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'image' => $post->getImage(),
                'profilePicture' => $profileImage,
                'username' => $post->getPostUser()->getSteamUsername(),
                'userId' => $post->getPostUser()->getId(),
                'gameName' => $post->getGamename(),
            ];
        }

        // Process following users' posts
        $following = $user->getFollowing();
        $followingUserIds = array_map(fn($followingUser) => $followingUser->getId(), $following->toArray());
        $followingPosts = $postRepository->findPostsByUsers($followingUserIds);

        $followingPostsWithImages = [];
        foreach ($followingPosts as $followingPost) {
            $steamID64 = $followingPost->getPostUser()->getSteamId64();
            $profileImage = $this->steamAppService->getUserProfileImage($steamID64);

            $followingPostsWithImages[] = [
                'id' => $followingPost->getId(),
                'content' => $followingPost->getContent(),
                'image' => $followingPost->getImage(),
                'profilePicture' => $profileImage,
                'username' => $followingPost->getPostUser()->getSteamUsername(),
                'userId' => $followingPost->getPostUser()->getId(),
                'gameName' => $followingPost->getGamename(),
            ];
        }

        return $this->render('home/index.html.twig', [
            'posts' => $postsWithImages,
            'followingPosts' => $followingPostsWithImages,
            'user' => $user,
            'banner' => $banner,
            'cloudinaryService' => $cloudinaryService,
        ]);
    }
}
