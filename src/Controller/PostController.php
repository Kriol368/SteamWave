<?php

// src/Controller/PostController.php

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

        // Render the template and pass the posts to it
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $posts,
        ]);
    }

    #[Route('/post/{id}', name: 'app_post_show', requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $entityManager, Request $request, SteamAppService $steamAppService): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        $comment = new Comment();
        $comment->setPost($post);
        $comment->setPublishedAt(new \DateTime());
        $comment->setUser($this->getUser());
        $comment->setIsChildComment(false);

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $id]);
        }
        $gameName = $steamAppService->getGameName((int) $post->getTag());


        return $this->render('post/single_post.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
            'gameName' => $gameName ?? 'Unknown Game',
        ]);
    }

    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function like(int $id, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        // Fetch the post by ID
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        // Increment likes
        $post->setNumLikes($post->getNumLikes() + 1);

        $em->persist($post);
        $em->flush();

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }


    #[Route('/post/new', name: 'app_post_new')]
    public function newPost(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $post = new Post();
        $user = $security->getUser(); // Get the logged-in user

        // Set the user on the post
        $post->setPostUser($user);

        // Create the form
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the uploaded image file
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Generate a unique filename
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Move the file to the uploads directory
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );

                    // Set the image filename in the Post entity
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    // Handle exception if file upload fails
                    $this->addFlash('error', 'Could not upload image.');
                }
            }

            // Retrieve and set the tag manually if it's unmapped in the form
            $tag = $form->get('tag')->getData();
            if ($tag) {
                $post->setTag($tag); // Ensure setTag method exists in Post entity
            }

            // Save the new post to the database
            $entityManager->persist($post);
            $entityManager->flush();

            // Add a flash message to notify the user
            $this->addFlash('success', 'Your post has been created successfully!');

            // Redirect to the post list page
            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/create_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
