<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\UserPost;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Repository\UserPostRepository;
use App\Service\CloudinaryService;
use App\Service\SteamAppService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SteamAppService $steamAppService;

    public function __construct(EntityManagerInterface $entityManager, SteamAppService  $steamAppService)
    {
        $this->entityManager = $entityManager;
        $this->steamAppService = $steamAppService;
    }

    #[Route('/post', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        // Fetch all posts from the database
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $posts,
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
        SteamAppService $steamAppService,
        CloudinaryService $cloudinaryService
    ): Response {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        // Create a new comment and bind it to the post
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setPublishedAt(new \DateTime());
        $comment->setUser($this->getUser());
        $comment->setIsChildComment(false);

        // Handle the comment form
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $id]);
        }

        // Get the game name and ID for redirection
        if ($post->getTag() != null){
            $gameId = (int) $post->getTag();
            $gameName = $steamAppService->getGameName($gameId);
        }else{
            $gameId = null;
            $gameName = null;
        }

        return $this->render('post/single_post.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
            'gameName' => $gameName ?? 'Unknown Game',
            'gameId' => $gameId ?? null,
            'cloudinaryService' => $cloudinaryService,
        ]);
    }

    #[Route('/like/{postId}', name: 'app_post_like', methods: ['POST'])]
    public function likePost(
        int $postId,
        PostRepository $postRepository,
        UserPostRepository $userPostRepository
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $post = $postRepository->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $userPost = $userPostRepository->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if (!$userPost) {
            // Create a new UserPost if it doesn't exist
            $userPost = new UserPost();
            $userPost->setUser($user);
            $userPost->setPost($post);
            $userPost->setLiked(true); // Mark as liked
            $this->entityManager->persist($userPost);
        } else {
            // Toggle the like status
            $userPost->setLiked(!$userPost->isLiked());
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_home');
    }

    // =====================================
    // en esta funcion miramos si hemos dado like
    #[Route('/like/get/', name: 'app_get_post_like')]
    public function getLike(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $entityManager = $doctrine->getManager();

        $data = json_decode($request->getContent(), true); // pillas el json
        $post = $entityManager->getRepository(Post::class)->find($data['postId']);

        $userPost = $entityManager->getRepository(UserPost::class)->findOneBy(array('user' => $currentUser, 'post' => $post));

        if (!$userPost) {
            return new JsonResponse(['message' => 'no interaction yet'], 200);
        }else {
            return new JsonResponse(['like' => $userPost->isLiked() ], 200);
        }
    }

    #[Route('/save/{postId}', name: 'app_post_save')]
    public function savePost(
        int $postId,
        PostRepository $postRepository,
        UserPostRepository $userPostRepository
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $post = $postRepository->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $userPost = $userPostRepository->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if (!$userPost) {
            // Create a new UserPost if it doesn't exist
            $userPost = new UserPost();
            $userPost->setUser($user);
            $userPost->setPost($post);
            $userPost->setSaved(true); // Mark as saved
            $this->entityManager->persist($userPost);
        } else {
            // Toggle the saved status
            $userPost->setSaved(!$userPost->isSaved());
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_home');
    }

    // =====================================
    // en esta funcion miramos si hemos dado save
    #[Route('/save/get/', name: 'app_get_post_save')]
    public function getSave(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $entityManager = $doctrine->getManager();

        $data = json_decode($request->getContent(), true); // pillas el json
        $post = $entityManager->getRepository(Post::class)->find($data['postId']);

        $userPost = $entityManager->getRepository(UserPost::class)->findOneBy(array('user' => $currentUser, 'post' => $post));

        if (!$userPost) {
            return new JsonResponse(['message' => 'no interaction yet'], 200);
        }else {
            return new JsonResponse(['save' => $userPost->isSaved() ], 200);
        }
    }

    #[Route('/post/new', name: 'app_post_new')]
    public function newPost(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        CloudinaryService $cloudinaryService,
    ): Response {
        $post = new Post();
        $user = $security->getUser();

        // Associate the logged-in user with the post
        $post->setPostUser($user);

        // Create and handle the post form
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file uploads
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();


            if ($imageFile) {
                try {
                    $imagePath = $imageFile->getRealPath();
                    $mimeType = $imageFile->getMimeType();

                    if (str_starts_with($mimeType, 'image/')) {
                        $uploadResult = $cloudinaryService->uploadPostFile($imagePath, 'posts', 'image');
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        $uploadResult = $cloudinaryService->uploadPostFile($imagePath, 'posts', 'video');
                    } else {
                        throw new \Exception('Unsupported file type.');
                    }

                    $fileUrl = $uploadResult['secure_url'] ?? null;

                    if ($fileUrl) {
                        $post->setImage($fileUrl); // Use a more generic field name like `setFileUrl` if needed
                    } else {
                        $this->addFlash('error', 'Failed to upload file to Cloudinary.');
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Could not upload file: ' . $e->getMessage());
                }
            }



            // Retrieve and set the tag manually
            $tag = $form->get('tag')->getData();
            if ($tag) {
                $post->setTag($tag);
            }

            if ($tag) {
                $game = $this->steamAppService->getGameName($tag);
                if ($game) {
                    $post->setGameName($game);
                }
            }
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Your post has been created successfully!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/create_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/post/delete/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function deletePost(int $id, Request $request, Security $security): RedirectResponse
    {
        // Get the current user
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Find the post by its ID
        $post = $this->entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        // Check if the current user is the owner of the post
        if ($post->getPostUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have permission to delete this post.');
        }

        // CSRF token validation
        if (!$this->isCsrfTokenValid('delete_post_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_post_show', ['id' => $id]);
        }

        // Delete all comments associated with the post
        $comments = $post->getComments();
        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
        }

        // Delete the post
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        $this->addFlash('success', 'Post and its comments have been deleted successfully.');

        // Redirect to the homepage or another route
        return $this->redirectToRoute('app_home');
    }

}
