<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
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

    #[Route('/send/message', name: 'app_message', methods: ['POST'])]
    public function sendMessage(ManagerRegistry $doctrine ,Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $entityManager = $doctrine->getManager();

        $data = json_decode($request->getContent(), true); // pillas el json
        $message = $data['message'] ?? null;    // le dices que de dentro del array saque la propiedad 'message' o sino que sea null.
        $chat = $entityManager->getRepository(Chat::class)->find($data['chat'] ?? null);   // lo mismo pero con el id del chat, pero en este caso sacamos la entidad directamente.

        if (($message || $chat) && (($message ==""|| $chat==""))) {

            if(!$this->verifyUser($currentUser, $chat)) { // verificación de usuario
                return new JsonResponse(['status' => 'error', 'message' => 'tonto o k¿'], 400); // si el usuario no está autorizado devuelve un error.
            }

            $newMessage = new Message($message, $currentUser, $chat); //se crea el mensaje.

            $entityManager->persist($newMessage);   // se guarda
            $entityManager->flush();    // y se envía

            //  TODO
            //  hacer que la página muestre el nuevo mensaje sin tener que actualizar.

            return new JsonResponse(['status' => 'success', 'message' => $message]);
        } else {
            //  este mensaje no me acaba de convencer.
            return new JsonResponse(['status' => 'error', 'message' => 'No message or chat provided'], 400);
        }
    }
}