<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
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
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => User::ROLE_ADMIN,
                    'User' => User::ROLE_USER,
                ],
                'label' => $this->translator->trans('Roles'),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'required' => false,
                'label' => $this->translator->trans('Password'),
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->translator->trans('Your password should be at least 6 characters'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'constraints' => [
                new Callback(function (User $user, ExecutionContextInterface $context) {
                    $password = $context->getRoot()->get('password')->getData();

                    // If the user is new (no ID yet) and the password is empty, add a violation
                    if (empty($password) && empty($user->getId())) {
                        $context->buildViolation($this->translator->trans('Please enter a password'))
                            ->atPath('password')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
