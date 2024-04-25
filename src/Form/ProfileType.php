<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => $this->translator->trans('Name'),
            ])
            ->add('email', null, [
                'label' => $this->translator->trans('Email'),
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => false,
                'label' => $this->translator->trans('Password'),
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Optional(),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->translator->trans('Your password should be at least 6 characters'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add("avatar", FileType::class, [
                'label' => $this->translator->trans('Avatar'),
                'required' => false,
                'constraints' => [
                    new Optional(),
                    new File([
                        'maxSize' => '1m',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => $this->translator->trans('Please upload a valid image file.'),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
