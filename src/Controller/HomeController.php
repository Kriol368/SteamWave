<?php

namespace App\Controller;

use App\Repository\ChatRepository;
use App\Repository\PostRepository;
use App\Service\CloudinaryService;
use App\Service\SteamAppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private SteamAppService $steamAppService;
    private ChatRepository $chatRepository;

    public function __construct(SteamAppService $steamAppService, ChatRepository $chatRepository)
    {
        $this->steamAppService = $steamAppService;
        $this->chatRepository = $chatRepository;
    }

    public function loadRecentChats($user): array
    {
        // TODO
        // hacer que el array se ordene por la fecha del ultimo mensaje envíado.
        // para que salgan mostrados los más recientes basicamnete.

        /*
        $chats = $this->chatRepository->findLastFiveFromUser($user);
        $sortedChats = [];
        for ($i = 0; $i<count($chats); $i++) {
            $sortedChats[$i] =
        }
        */

        return $this->chatRepository->findLastFiveFromUser($user);
    }

    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepository, CloudinaryService $cloudinaryService): Response
    {
        $posts = $postRepository->findBy([], ['publishedAt' => 'DESC']);
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirect to login if not authenticated
        }

        $recentChats = $this->loadRecentChats($user);

        // Fetch banner
        $bannerGameId = $user->getBanner();
        $banner = $bannerGameId ? $this->steamAppService->getGameBannerUrl($bannerGameId) : null;

        // Process user's posts
        $postsWithImages = [];
        foreach ($posts as $post) {
            $steamID64 = $post->getPostUser()->getSteamId64();

            $postsWithImages[] = [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'image' => $post->getImage(),
                'profilePicture' => $post->getPostUser()->getPfp(),
                'username' => $post->getPostUser()->getName(),
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

            $followingPostsWithImages[] = [
                'id' => $followingPost->getId(),
                'content' => $followingPost->getContent(),
                'image' => $followingPost->getImage(),
                'profilePicture' => $followingPost->getPostUser()->getPfp(),
                'username' => $followingPost->getPostUser()->getName(),
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
            'recentChats' => $recentChats
        ]);
    }
}
