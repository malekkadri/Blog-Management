<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostController extends AbstractController
{
    private $postRepository;
    private $entityManager;
    private $translator;
    private $emailService;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager, TranslatorInterface $translator, EmailService $emailService)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->emailService = $emailService;
    }

    /**
     * @Route("/posts/{slug}", name="app_post", methods={"GET", "POST"}))
     */
    public function show(Request $request, string $slug): Response
    {
        $locale = $request->getLocale();
        $userId = $this->getUser() ? $this->getUser()->getId() : null;

        $post = $this->postRepository->findPostBySlug($slug, $locale, $userId);

        if (!$post) {
            throw $this->createNotFoundException('No post found for slug ' . $slug);
        }

        $likesCount = $this->postRepository->getLikesCount($post->getId());

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                throw $this->createAccessDeniedException('You must be logged in to comment.');
            }

            $comment->setPost($post);
            $comment->setAuthor($this->getUser());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->emailService->notifyPostAuthorAboutNewComment($post, $comment, $locale);

            $this->addFlash('success', $this->translator->trans('Your comment was saved successfully.'));

            return $this->redirectToRoute('app_post', ['slug' => $slug]);
        }

        return $this->render('pages/post.html.twig', [
            'post' => $post,
            'likesCount' => $likesCount,
            'form' => $form->createView()
        ]);
    }
}
