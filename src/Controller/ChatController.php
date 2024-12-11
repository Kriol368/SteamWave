<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Form\ChatFormType;
use App\Repository\ChatRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $this->entityManager = $entityManager;
    }

    #[Route('/chat', name: 'app_chat')]
    public function index(): Response
    {
        $currentUser = $this->getUser();
        $chats = $this->chatRepository->findByUser($currentUser);

        return $this->render('chat/index.html.twig', [
            'chats' => $chats,
        ]);
    }

    #[Route('/chat/create', name: 'app_chat_create')]
    public function create(Request $request): Response
    {
        $chat = new Chat();
        $currentUser = $this->security->getUser();
        $form = $this->createForm(ChatFormType::class, $chat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chat->addUser($currentUser);
            $chat->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($chat);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_chat');
        }

        return $this->render('chat/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/chat/{id}', name: 'chat_show')]
    public function show(int $id, ChatRepository $chatRepository): Response
    {
        $currentUser = $this->getUser();
        $userId = $this->getUser()->getId();
        $chat = $chatRepository->find($id);

        if (!$chat) {
            throw $this->createNotFoundException('Chat not found');
        }

        $messages = $chat->getMessages();
        return $this->render('chat/show.html.twig', [
            'user' => $currentUser,
            'chat' => $chat,
            'messages' => $messages,
        ]);
    }
}
