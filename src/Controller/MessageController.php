<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message as MiMessage;
use App\Entity\User;
use App\Repository\ChatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class MessageController extends AbstractController
{
    public function __construct(ChatRepository $chatRepository, Security $security, EntityManagerInterface $entityManager)
    {
        $this->chatRepository = $chatRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    //  esta funcion verifica que el usuario introducido es parte del chat introducido.
    public function verifyUser($user, $chat): bool
    {
        $userChats = $this->chatRepository->findByUser($user); // Array con los chats que tiene el usuario.

        for ($i = 0; $i < count($userChats); $i++) { // cada iteración compara las id del chat actual con el correspondiente.
            if ($userChats[$i]->getId() == $chat->getId()) {
                return true; // si coinciden, el usuario es valido.
            }
        }
        return false; // si no, pues no.
    }

    // esta funcion verifica que el mensaje sea valido para ser almacenado.
    public function verifyMessage(MiMessage $a): bool
    {
        // unicamente revisa que ni el texto ni el id sea una string vacía.
        // se podrían añadir más cosas en el futuro (no creo).

        if(!$a->getText()){
            return false;
        }
        else return true;
    }

    #[Route('/send/message', name: 'app_message', methods: ['POST'])]
    public function sendMessage(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $entityManager = $doctrine->getManager();

        // Obtener los datos del request
        $data = json_decode($request->getContent(), true);

        // Validar que los datos existen
        $message = $data['message'] ?? null;
        $chatId = $data['chat'] ?? null;

        if (!$message || !$chatId) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing message or chat'], 400);
        }

        $chat = $entityManager->getRepository(Chat::class)->find($chatId);

        if (!$chat) {
            return new JsonResponse(['status' => 'error', 'message' => 'Chat not found'], 400);
        }

        $newMessage = new MiMessage($message, $currentUser, $chat);

        if ($this->verifyMessage($newMessage) && $this->verifyUser($currentUser, $chat)) {
            $entityManager->persist($newMessage);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => $message]);
        } else {
            if (!$this->verifyUser($currentUser, $chat)) {
                return new JsonResponse(['status' => 'error', 'message' => 'User not part of the chat'], 400);
            }

            if (!$this->verifyMessage($newMessage)) {
                return new JsonResponse(['status' => 'error', 'message' => 'Message not valid'], 400);
            }

            return new JsonResponse(['status' => 'error', 'message' => 'Unknown error'], 400);
        }
    }

}