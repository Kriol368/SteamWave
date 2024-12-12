<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Service\CloudinaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private HttpClientInterface $httpClient;
    private CloudinaryService $cloudinaryService;

    public function __construct(EmailVerifier $emailVerifier, HttpClientInterface $httpClient, CloudinaryService $cloudinaryService)
    {
        $this->emailVerifier = $emailVerifier;
        $this->httpClient = $httpClient;
        $this->cloudinaryService = $cloudinaryService;
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

            // Retrieve Steam profile URL
            $steamProfileUrl = $form->get('steamProfileUrl')->getData();
            if ($steamProfileUrl) {
                // Fetch Steam user data
                $steamData = $this->fetchSteamData($steamProfileUrl);

                if ($steamData) {
                    // Fetch profile image URL from Steam
                    $profileImageUrl = $steamData['avatarfull'] ?? null;
                    if ($profileImageUrl) {
                        // Upload the profile image to Cloudinary
                        $uploadedImageUrl = $this->cloudinaryService->uploadPfp($profileImageUrl);
                        if ($uploadedImageUrl) {
                            // Set the uploaded Cloudinary URL in the user entity
                            $user->setPfp($uploadedImageUrl);
                        }
                    }

                    // Set Steam-related information
                    $user->setSteamID64($steamData['steamid']);
                    $user->setSteamUsername($steamData['personaname'] ?? ''); // Set username if available
                }
            }

            // Save the user entity
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
        $steamApiKey = $_ENV['STEAM_API_KEY']; // Use an environment variable
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
        if (preg_match('/https?:\/\/steamcommunity\.com\/profiles\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/https?:\/\/steamcommunity\.com\/id\/([^\/]+)/', $url, $matches)) {
            $vanityName = $matches[1];
            return $this->resolveVanityURLToSteamID($vanityName);
        }

        return null;
    }

    private function resolveVanityURLToSteamID(string $vanityName): ?string
    {
        $steamApiKey = $_ENV['STEAM_API_KEY'];
        $response = $this->httpClient->request('GET', "http://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key={$steamApiKey}&vanityurl={$vanityName}");
        $data = $response->toArray();

        return $data['response']['success'] == 1 ? $data['response']['steamid'] : null;
    }
}
