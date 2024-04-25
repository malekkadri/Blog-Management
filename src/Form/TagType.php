<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraints = [
            new NotBlank(),
            new Length(['max' => 255]),
        ];

        $builder
            ->add("name_en", TextType::class, [
                'label' => $this->translator->trans('Name') . ' EN',
                'required' => true,
                'constraints' => $constraints,
            ])
            ->add("name_hr", TextType::class, [
                'label' => $this->translator->trans('Name') . ' HR',
                'required' => true,
                'constraints' => $constraints,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
