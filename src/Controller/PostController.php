<?php

// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

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
            'posts' => $posts,  // Pass the posts to the template
        ]);
    }

    #[Route('/post/new', name: 'app_post_new')]
    public function newPost(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $post = new Post();
        $user = $security->getUser();  // Get the logged-in user automatically

        // Set the user on the post
        $post->setPostUser($user);

        // Create the form
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
