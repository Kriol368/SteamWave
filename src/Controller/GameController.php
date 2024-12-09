<?php
namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewFormType;
use App\Repository\ReviewRepository;
use App\Service\SteamAppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    private SteamAppService $steamAppService;

    public function __construct(SteamAppService $steamAppService)
    {
        $this->steamAppService = $steamAppService;
    }

    #[Route('/game/{appId}', name: 'app_game')]
    public function index(int $appId, Request $request, ReviewRepository $reviewRepository, EntityManagerInterface $em): Response
    {
        // Fetch detailed game information
        $gameDetails = $this->steamAppService->getGameDetails($appId);

        $reviews = $reviewRepository->findBy(['game' => (string) $appId], ['publishedAt' => 'DESC']);

        $averageWaves = $reviewRepository->getAverageWavesForGame((string) $appId);

        $review = new Review();
        $form = $this->createForm(ReviewFormType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setGame((string) $appId); // Use the appId to link the review to the game
            $review->setUser($this->getUser()); // Attach the logged-in user
            $review->setPublishedAt(new \DateTimeImmutable()); // Set the current date/time
            $em->persist($review);
            $em->flush();

            // Redirect to prevent resubmission of the form
            return $this->redirectToRoute('app_game', ['appId' => $appId]);
        }

        if (!$gameDetails) {
            throw $this->createNotFoundException("Game with ID $appId not found.");
        }

        return $this->render('game/index.html.twig', [
            'game' => $gameDetails,
            'reviews' => $reviews,
            'reviewForm' => $form->createView(),
            'averageWaves' => $averageWaves,
        ]);
    }
}
