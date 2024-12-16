<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class MessageController extends AbstractController
{
    #[Route('/send/message', name: 'app_message', methods: ['POST'])]
    public function sendMessage(ManagerRegistry $doctrine ,Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $currentUser = $this->getUser();

        $data = json_decode($request->getContent(), true); // pillas el json
        $message = $data['message'] ?? null;    // le dices que dentro del array que saque la propiedad 'message' o sino que sea null. en este caso la única que hay.

        if ($message) {

            //  TODO
            //  añadir seguridad para que ningun usuario qualquiera pueda enviar mensajes en qualquier cht.

            $chat = $entityManager->getRepository(Chat::class)->find(1);
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