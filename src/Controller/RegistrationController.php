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
                dump($form->get('steamProfileUrl')->getData());
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
                    ->from(new Address('steamwavebusiness@gmail.com', 'SteamWave'))
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

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');
        return $this->redirectToRoute('app_home'); // or any appropriate route after verification
    }


    private function fetchSteamData(string $steamProfileUrl): ?array
    {
        // Replace STEAM_API_KEY with your actual Steam Web API key
        $steamApiKey = '562D15EBA45FEC235C627957E91296DE';
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
        // Check for profile URL with SteamID64
        if (preg_match('/https?:\/\/steamcommunity\.com\/profiles\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        // Check for vanity URL
        if (preg_match('/https?:\/\/steamcommunity\.com\/id\/([^\/]+)/', $url, $matches)) {
            $vanityName = $matches[1];
            return $this->resolveVanityURLToSteamID($vanityName); // Resolves the vanity URL to a SteamID64
        }

        return null;
    }

    private function resolveVanityURLToSteamID(string $vanityName): ?string
    {
        $steamApiKey = '562D15EBA45FEC235C627957E91296DE';
        $response = $this->httpClient->request('GET', "http://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key={$steamApiKey}&vanityurl={$vanityName}");
        $data = $response->toArray();

        return $data['response']['success'] == 1 ? $data['response']['steamid'] : null;
    }

}
