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

class FavoriteController extends AbstractController
{
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/posts/{id}/add-favorite", name="app_post_add_favorite", methods={"POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function add(Request $request, Post $post): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('add_favorite' . $post->getId(), $request->request->get('_token'))) {
            $this->postRepository->addPostToFavorites($post->getId(), $user->getId());
        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }

    /**
     * @Route("/posts/{id}/remove-favorite", name="app_post_remove_favorite", methods={"POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function remove(Request $request, Post $post): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('remove_favorite' . $post->getId(), $request->request->get('_token'))) {
            $this->postRepository->removePostFromFavorites($post->getId(), $user->getId());
        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_home'));
    }
}
