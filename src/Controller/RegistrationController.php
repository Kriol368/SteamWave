<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private HttpClientInterface $httpClient;

    public function __construct(EmailVerifier $emailVerifier, HttpClientInterface $httpClient)
    {
        $this->emailVerifier = $emailVerifier;
        $this->httpClient = $httpClient;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Retrieve Steam account information
            $steamProfileUrl = $form->get('steamProfileUrl')->getData();
            if ($steamProfileUrl) {
                $steamData = $this->fetchSteamData($steamProfileUrl);
                if ($steamData) {
                    $user->setSteamID64($steamData['steamid']);
                    $user->setSteamUsername($steamData['personaname']);
                }
            }

            // Save the user
            $entityManager->persist($user);
            $entityManager->flush();

            // Send email verification
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('kriol368@gmail.com', 'Kriol'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function fetchSteamData(string $steamProfileUrl): ?array
    {
        // Replace STEAM_API_KEY with your actual Steam Web API key
        $steamApiKey = 'STEAM_API_KEY';
        $steamId = $this->extractSteamIDFromUrl($steamProfileUrl);

        if ($steamId) {
            $response = $this->httpClient->request('GET', "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steamApiKey}&steamids={$steamId}");
            $data = $response->toArray();

            if (isset($data['response']['players'][0])) {
                return $data['response']['players'][0];
            }
        }

        return null;
    }

    private function extractSteamIDFromUrl(string $url): ?string
    {
        // Extract Steam ID from the profile URL
        preg_match('/https?:\/\/steamcommunity\.com\/profiles\/(\d+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
