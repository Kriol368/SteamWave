<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all posts (or add pagination later if necessary)
        $posts = $entityManager->getRepository(Post::class)->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/new', name: 'app_post_new')]
    public function newPost(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload if there's an image or video
            $file = $form->get('image')->getData();
            if ($file) {
                $newFilename = uniqid().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('media_directory'), // Define this in config/services.yaml
                    $newFilename
                );
                $post->setImage($newFilename);
            }

            // Set other default values for the post
            $post->setNumLikes(0);
            $post->setPublishedAt(new \DateTime());
            $post->setPostUser($this->getUser());

            // Persist and flush to save the post
            $entityManager->persist($post);
            $entityManager->flush();

            // Redirect to post index or a detailed view for the new post
            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
