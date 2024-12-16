<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{

    #[Route('/send/message', name: 'app_message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true); // pillas el json
        $message = $data['message'] ?? null;    // le dices que dentro del array que saque la propiedad 'message' o sino que sea null. en este caso la única que hay.

        if ($message) {
            $chat = $this->entityManager->getRepository(Chat::class)->find("1");
            new Message($message, $this->getUser(), $chat);

            return new JsonResponse(['status' => 'success', 'message' => $message]);
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'No message provided'], 400);
        }
    }
}
