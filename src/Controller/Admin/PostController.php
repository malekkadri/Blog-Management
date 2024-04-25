<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Services\FileService;
use DateTime;
use DateTimeImmutable;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/posts")
 */
class PostController extends AbstractController
{
    private $params;
    private $fileService;
    private $translator;

    public function __construct(ParameterBagInterface $params, FileService $fileService, TranslatorInterface $translator)
    {
        $this->params = $params;
        $this->fileService = $fileService;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="app_post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $locale = $request->getLocale();

        $query = $postRepository->getPaginationQuery($locale);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="app_post_new", methods={"GET", "POST"})
     */
    public function new(Request $request, PostRepository $postRepository, TagRepository $tagRepository): Response
    {
        $locales = $this->params->get('available_locales');

        $post = new Post();

        $form = $this->createForm(PostType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $post->setPublishedAt(new DateTimeImmutable($form->get('published_at')->getData()));
            $post->setImage($this->fileService->storeFile($form->get('image')->getData(), 'post-images'));

            $this->fileService->optimizeImage($post->getImage());

            $tags = $tagRepository->findBy(['id' => $form->get('tags')->getData()]);

            foreach ($tags as $tag) {
                $post->addTag($tag);
            }

            foreach ($locales as $locale) {
                $translation = new PostTranslation();
                $translation->setLocale($locale);
                $translation->setTitle($form->get("title_$locale")->getData());
                $translation->setContent($form->get("content_$locale")->getData());
                $post->addTranslation($translation);
            }

            $postRepository->save($post, true);

            $this->addFlash('success', $this->translator->trans('Post created successfully!'));

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_post_show", methods={"GET"})
     */
    public function show(Request $request, Post $post, PostRepository $postRepository): Response
    {
        return $this->render('admin/post/show.html.twig', [
            'post' => $postRepository->getPostData($post, $request->getLocale()),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_post_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Post $post, PostRepository $postRepository, TagRepository $tagRepository): Response
    {
        $locales = $this->params->get('available_locales');

        $postData = $postRepository->getPostData($post, $request->getLocale());

        $form = $this->createForm(PostType::class, $postData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPublishedAt(new DateTimeImmutable($form->get('published_at')->getData()));
            $post->setUpdatedAt(new DateTime());

            if ($form->get('image')->getData()) {
                if ($post->getImage()) {
                    $this->fileService->removeFile($post->getImage());
                }

                $post->setImage($this->fileService->storeFile($form->get('image')->getData(), 'post-images'));

                $this->fileService->optimizeImage($post->getImage());
            }

            foreach ($locales as $locale) {
                $translation = $post->getTranslations()->filter(function (PostTranslation $translation) use ($locale) {
                    return $translation->getLocale() === $locale;
                })->first();

                $translation->setTitle($form->get("title_$locale")->getData());
                $translation->setContent($form->get("content_$locale")->getData());
            }

            $tags = $tagRepository->findBy(['id' => $form->get('tags')->getData()]);
            $post->getTags()->clear();

            foreach ($tags as $tag) {
                $post->addTag($tag);
            }

            $postRepository->save($post, true);

            $this->addFlash('success', $this->translator->trans('Post updated successfully!'));

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/post/edit.html.twig', [
            'post' => $postData,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post, PostRepository $postRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $postRepository->remove($post, true);

            if ($post->getImage()) {
                $this->fileService->removeFile($post->getImage());
            }
        }

        $this->addFlash('success', $this->translator->trans('Post deleted successfully!'));

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
