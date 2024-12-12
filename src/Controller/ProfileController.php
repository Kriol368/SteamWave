<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserPostRepository;
use App\Repository\UserRepository;
use App\Service\CloudinaryService;
use App\Service\SteamAppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    private SteamAppService $steamAppService;
    private Security $security;
    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private UserPostRepository $userPostRepository;

    public function __construct(
        SteamAppService $steamAppService,
        Security $security,
        PostRepository $postRepository,
        UserRepository $userRepository,
        UserPostRepository $userPostRepository,
    ) {
        $this->steamAppService = $steamAppService;
        $this->security = $security;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->userPostRepository = $userPostRepository;
    }

    #[Route('/user/{id}/games-list', name: 'user_specific_games_list', methods: ['GET'])]
    public function getUserGamesList(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse(['error' => 'Invalid user or SteamID64 not found'], Response::HTTP_BAD_REQUEST);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }

    #[Route('/user/games-list', name: 'user_logged_games_list', methods: ['GET'])]
    public function getLoggedUserGamesList(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user->getSteamID64()) {
            return new JsonResponse(['error' => 'User not authenticated or SteamID64 not found'], Response::HTTP_BAD_REQUEST);
        }

        $games = $this->steamAppService->getUserGames($user->getSteamID64());

        return new JsonResponse($games);
    }

        #[Route('/profile/{userId}', name: 'view_profile', defaults: ['userId' => null])]
    public function viewProfile(?int $userId,CloudinaryService $cloudinaryService): Response
    {
        $user = $userId ? $this->userRepository->find($userId) : $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // Fetch posts by the user
        $posts = $this->postRepository->findBy(['postUser' => $user->getId()], ['publishedAt' => 'DESC']);

        // Prepare user's own posts with additional data
        $postsWithImages = $this->getWithDetails($posts);

        // Fetch saved posts by the user
        $savedPosts = $this->userPostRepository->findBy(['user' => $user, 'saved' => true]);

        // Prepare saved posts with additional data
        $savedPostsWithImages = $this->getWithDetails($savedPosts);

        // Fetch liked posts by the user
        $likedPosts = $this->userPostRepository->findBy(['user' => $user, 'liked' => true]);

        // Prepare liked posts with additional data
        $likedPostsWithImages = $this->getWithDetails($likedPosts);

        // Fetch the user's banner game ID and retrieve the banner URL
        $bannerGameId = $user->getBanner();
        $banner = $bannerGameId ? $this->steamAppService->getGameBannerUrl($bannerGameId) : null;

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $postsWithImages,
            'savedPosts' => $savedPostsWithImages,
            'likedPosts' => $likedPostsWithImages,
            'isOwnProfile' => $user === $this->getUser(),
            'banner' => $banner,
            'cloudinaryService' => $cloudinaryService,
        ]);
    }

    #[Route('/profile/{userId}/followers', name: 'user_followers')]
    public function viewFollowers(int $userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // Get the followers of the user
        $followers = $user->getFollowers();

        return $this->render('profile/followers.html.twig', [
            'user' => $user,
            'followers' => $followers,
        ]);
    }

    #[Route('/profile/{userId}/following', name: 'user_following')]
    public function viewFollowing(int $userId, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // Get the users that this user is following
        $following = $user->getFollowing();

        return $this->render('profile/following.html.twig', [
            'user' => $user,
            'following' => $following,
        ]);
    }


    #[Route('/profile/follow/{id}', name: 'follow_user')]
    public function follow(int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): RedirectResponse
    {
        $userToFollow = $userRepository->find($id);

        if (!$userToFollow) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('view_profile', ['userId' => $id]);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToFollow !== $currentUser && !$currentUser->getFollowing()->contains($userToFollow)) {
            $currentUser->addFollowing($userToFollow);
            $entityManager->flush();
            $this->addFlash('success', 'You are now following ' . $userToFollow->getName() . '.');
        }

        return $this->redirectToRoute('view_profile', ['userId' => $userToFollow->getId()]);
    }

    #[Route('/profile/unfollow/{id}', name: 'unfollow_user')]
    public function unfollow(int $id, EntityManagerInterface $entityManager, UserRepository $userRepository): RedirectResponse
    {
        $userToUnfollow = $userRepository->find($id);

        if (!$userToUnfollow) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('view_profile', ['userId' => $id]);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToUnfollow !== $currentUser && $currentUser->getFollowing()->contains($userToUnfollow)) {
            $currentUser->removeFollowing($userToUnfollow);
            $entityManager->flush();
            $this->addFlash('success', 'You have unfollowed ' . $userToUnfollow->getName() . '.');
        }

        return $this->redirectToRoute('view_profile', ['userId' => $userToUnfollow->getId()]);
    }


    /**
     * @param array $posts
     * @return array|array[]
     */
    public function getWithDetails(array $posts): array
    {
        return array_map(function ($postEntity) {
            // Determine if we are dealing with a Post or a UserPost
            $post = ($postEntity instanceof \App\Entity\UserPost) ? $postEntity->getPost() : $postEntity;

            $steamID64 = $post->getPostUser()->getSteamId64();
            return [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'tag' => $this->steamAppService->getGameName($post->getTag()),
                'image' => $post->getImage(),
                'profilePicture' => $post->getPostUser()->getPfp(),
                'username' => $post->getPostUser()->getSteamUsername(),
                'userId' => $post->getPostUser()->getId(),
                'gameName' => $post->getGamename(),
            ];
        }, $posts);
    }


}
