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

    #[Route('/comment/{id}/like', name: 'comment_like', methods: ['POST'])]
    public function like(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        // Check if the user already liked the comment
        if ($comment->hasUserLiked($user)) {
            return new JsonResponse(['error' => 'Already liked'], 400);
        }

        // Create a new like
        $like = new CommentLike();
        $like->setComment($comment);
        $like->setUser($user);

        $entityManager->persist($like);
        $entityManager->flush();

        return new JsonResponse([
            'likes' => $comment->getLikes()->count()
        ]);
    }


}
