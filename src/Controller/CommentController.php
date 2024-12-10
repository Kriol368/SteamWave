<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CommentController extends AbstractController
{
    #[Route('/comment/delete/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(
        int $id,
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        // Get the current user
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Find the comment by its ID
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        // Check if the current user is the owner of the comment or the post owner
        if ($comment->getUser() !== $user && $comment->getPost()->getPostUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have permission to delete this comment.');
        }

        // CSRF token validation
        if (!$this->isCsrfTokenValid('delete_comment_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
        }

        // Remove the comment from the database
        $entityManager->remove($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment has been deleted successfully.');

        // Redirect back to the post where the comment was deleted
        return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
    }


    // src/Controller/CommentController.php

    #[Route('/comment/{id}/like', name: 'comment_like', methods: ['POST','GET'])]
    public function like(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse(['error' => 'Unauthorized'], 403);
            }

            // Manually fetch the comment using the repository
            $comment = $entityManager->getRepository(Comment::class)->find($id);
            if (!$comment) {
                return new JsonResponse(['error' => 'Comment not found'], 404);
            }

            $likeRepository = $entityManager->getRepository(CommentLike::class);
            $existingLike = $likeRepository->findOneBy([
                'comment' => $comment,
                'user' => $user
            ]);

            if ($existingLike) {
                // Unlike the comment if it was already liked
                $entityManager->remove($existingLike);
                $entityManager->flush();

                return new JsonResponse([
                    'success' => 'Like removed',
                    'likes' => $comment->getLikes()->count()
                ]);
            }

            // Create a new like
            $like = new CommentLike();
            $like->setComment($comment);
            $like->setUser($user);

            $entityManager->persist($like);
            $entityManager->flush();

            return new JsonResponse([
                'success' => 'Comment liked',
                'likes' => $comment->getLikes()->count()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/comment/{id}/unlike', name: 'comment_unlike', methods: ['POST','GET'])]
    public function unlike(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse(['error' => 'Unauthorized'], 403);
            }

            $likeRepository = $entityManager->getRepository(CommentLike::class);

            // Check if the like exists
            $existingLike = $likeRepository->findOneBy([
                'comment' => $comment,
                'user' => $user
            ]);

            if (!$existingLike) {
                return new JsonResponse(['error' => 'You have not liked this comment'], 400);
            }

            // Remove the like from the database
            $entityManager->remove($existingLike);
            $entityManager->flush();

            return new JsonResponse([
                'success' => 'Like removed',
                'likes' => $comment->getLikes()->count() // Optionally show the updated like count
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }



}
