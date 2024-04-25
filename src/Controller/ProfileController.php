<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use App\Services\FileService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private $userRepository;
    private $passwordHasher;
    private $translator;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile", name="app_profile", methods={"GET"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function show(Request $request): Response
    {
        $locale = $request->getLocale();

        $user = $this->userRepository->getProfileData($this->getUser()->getId(), $locale);

        return $this->render('pages/profile.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/edit", name="app_profile_edit", methods={"GET", "POST"}))
     * @IsGranted("IS_AUTHENTICATED")
     */
    public function edit(Request $request, FileService $fileService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($password = $form->get('password')->getData()) {
                $user->setPassword(
                    $this->passwordHasher->hashPassword($user, $password)
                );
            }

            if ($avatar = $form->get('avatar')->getData()) {
                if ($user->getAvatar()) {
                    $fileService->removeFile($user->getAvatar());
                }

                $user->setAvatar($fileService->storeFile($avatar, 'avatars'));

                $fileService->optimizeImage($user->getAvatar(), 500, 500);
            }

            $this->userRepository->add($user, true);

            $this->addFlash('success', $this->translator->trans('Profile updated successfully.'));

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('pages/profile-edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
