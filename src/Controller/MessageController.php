<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class MessageController extends AbstractController
{
    public function verifyUser(User $user, int $chatId): Boolean
    {
        if(){

        }
    }
    #[Route('/send/message', name: 'app_message', methods: ['POST'])]
    public function sendMessage(ManagerRegistry $doctrine ,Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $entityManager = $doctrine->getManager();

        $data = json_decode($request->getContent(), true); // pillas el json
        $message = $data['message'] ?? null;    // le dices que dentro del array que saque la propiedad 'message' o sino que sea null. en este caso la única que hay.
        $chat = $entityManager->getRepository(Chat::class)->find($data['chat'] ?? null);   // lo mismo pero con el id del chat, pero en este caso sacamos la entidad directamente.

        if ($message || $chat) {

            //  TODO
            //  añadir seguridad para que ningun usuario qualquiera pueda enviar mensajes en qualquier cht.



            $newMessage = new Message($message, $currentUser, $chat);

            $entityManager->persist($newMessage);
            $entityManager->flush();

            //  TODO
            //  hacer que la página muestre el nuevo mensaje sin tener que actualizar.

            return new JsonResponse(['status' => 'success', 'message' => $message]);
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'No message provided'], 400);
        }
    }
}