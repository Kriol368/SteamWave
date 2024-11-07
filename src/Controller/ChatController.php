<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Form\ChatFormType;
use App\Repository\ChatRepository;
use Doctrine\ORM\EntityManagerInterface; // Import the EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ChatController extends AbstractController
{
    private $chatRepository;
    private $security;
    private $entityManager;

    public function __construct(ChatRepository $chatRepository, Security $security, EntityManagerInterface $entityManager)
    {
        $this->chatRepository = $chatRepository;
        $this->security = $security;
        $this->entityManager = $entityManager; // Inject the EntityManagerInterface
    }

    #[Route('/chat', name: 'app_chat')]
    public function index(): Response
    {
        $currentUser = $this->security->getUser();

        // Fetch chats where the current user is a participant
        $chats = $this->chatRepository->findByUser($currentUser);

        return $this->render('chat/index.html.twig', [
            'controller_name' => 'ChatController',
            'chats' => $chats,
        ]);
    }

    #[Route('/chat/create', name: 'app_chat_create')]
    public function create(Request $request): Response
    {
        // Create a new Chat entity
        $chat = new Chat();
        $currentUser = $this->security->getUser();

        // Create the form using the ChatFormType class
        $form = $this->createForm(ChatFormType::class, $chat);

        // Handle the request (populate the form with submitted data)
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Add the current user to the chat
            $chat->addUser($currentUser);

            // Persist the chat to the database
            $this->entityManager->persist($chat); // Use the injected EntityManager
            $this->entityManager->flush(); // Flush changes to the database

            // Redirect to the list of chats after successful creation
            return $this->redirectToRoute('app_chat');
        }

        // Render the form in the template, passing 'form' variable
        return $this->render('chat/create.html.twig', [
            'form' => $form->createView(), // Pass the form variable
        ]);
    }

    #[Route('/chat/{id}', name: 'chat_show')]
    public function show(Chat $chat): Response
    {
        // Now Symfony will automatically fetch the Chat entity by its ID
        return $this->render('chat/show.html.twig', [
            'chat' => $chat,
        ]);
    }
}
