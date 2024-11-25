<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Service\SteamAppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostController extends AbstractController
{
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
        SteamAppService $steamAppService
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
        $gameId = (int) $post->getTag();
        $gameName = $steamAppService->getGameName($gameId);

        return $this->render('post/single_post.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
            'gameName' => $gameName ?? 'Unknown Game',
            'gameId' => $gameId ?? null,
        ]);
    }

    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function like(
        int $id,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        // Increment the number of likes
        $post->setNumLikes($post->getNumLikes() + 1);

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }

    #[Route('/post/new', name: 'app_post_new')]
    public function newPost(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security
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
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload image.');
                }
            }

            // Retrieve and set the tag manually
            $tag = $form->get('tag')->getData();
            if ($tag) {
                $post->setTag($tag);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Your post has been created successfully!');

            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/create_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
