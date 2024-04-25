<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/posts/{id}/like", name="app_post_like", methods={"POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function like(Request $request, Post $post): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('like' . $post->getId(), $request->request->get('_token'))) {
            $this->postRepository->likePost($post->getId(), $user->getId());
        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }

    /**
     * @Route("/posts/{id}/unlike", name="app_post_unlike", methods={"POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function unlike(Request $request, Post $post): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('unlike' . $post->getId(), $request->request->get('_token'))) {
            $this->postRepository->unlikePost($post->getId(), $user->getId());
        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }
}
