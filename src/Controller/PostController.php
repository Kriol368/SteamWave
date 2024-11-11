<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function newPost(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Define the Post entity and form
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload and form data
            $file = $form->get('image')->getData();
            if ($file) {
                $newFilename = uniqid().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('media_directory'),
                    $newFilename
                );
                $post->setImage($newFilename);
            }

            $post->setNumLikes(0);
            $post->setPublishedAt(new \DateTime());
            $post->setPostUser($this->getUser());

            // Save the post
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post');
        }

        // Render the template, passing controller_name
        return $this->render('post/create_post.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'PostController', // Pass this as needed
        ]);
    }



}
