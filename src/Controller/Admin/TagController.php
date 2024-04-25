<?php

namespace App\Controller\Admin;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Form\TagType;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/tags")
 */
class TagController extends AbstractController
{
    private $params;
    private $translator;

    public function __construct(ParameterBagInterface $params, TranslatorInterface $translator)
    {
        $this->params = $params;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="app_tag_index", methods={"GET"})
     */
    public function index(TagRepository $tagRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $locale = $request->getLocale();

        $query = $tagRepository->getPaginationQuery($locale);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/tag/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="app_tag_new", methods={"GET", "POST"})
     */
    public function new(Request $request, TagRepository $tagRepository): Response
    {
        $locales = $this->params->get('available_locales');

        $tag = new Tag();

        $form = $this->createForm(TagType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setUser($this->getUser());

            foreach ($locales as $locale) {
                $translation = new TagTranslation();
                $translation->setLocale($locale);
                $translation->setName($form->get("name_$locale")->getData());
                $tag->addTranslation($translation);
            }

            $tagRepository->add($tag, true);

            $this->addFlash('success', $this->translator->trans('Tag created successfully!'));

            return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_tag_show", methods={"GET"})
     */
    public function show(Tag $tag, TagRepository $tagRepository): Response
    {
        return $this->render('admin/tag/show.html.twig', [
            'tag' => $tagRepository->getTagData($tag),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_tag_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Tag $tag, TagRepository $tagRepository, EntityManagerInterface $entityManager): Response
    {
        $locales = $this->params->get('available_locales');

        $tagData = $tagRepository->getTagData($tag);

        $form = $this->createForm(TagType::class, $tagData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setUpdatedAt(new DateTime());

            foreach ($locales as $locale) {
                $translation = $tag->getTranslations()->filter(function (TagTranslation $translation) use ($locale) {
                    return $translation->getLocale() === $locale;
                })->first();

                $translation->setName($form->get("name_$locale")->getData());
            }

            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('Tag updated successfully!'));

            return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/tag/edit.html.twig', [
            'tag' => $tagData,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_tag_delete", methods={"POST"})
     */
    public function delete(Request $request, Tag $tag, TagRepository $tagRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tag->getId(), $request->request->get('_token'))) {
            $tagRepository->remove($tag, true);
        }

        $this->addFlash('success', $this->translator->trans('Tag deleted successfully!'));

        return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
    }
}
