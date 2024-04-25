<?php

namespace App\Form;

use App\Repository\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostType extends AbstractType
{
    private $tagRepository;
    private $translator;

    public function __construct(TagRepository $tagRepository, TranslatorInterface $translator)
    {
        $this->tagRepository = $tagRepository;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isUpdate = isset($options['data']);

        $builder
            ->add("title_en", TextType::class, [
                'label' => $this->translator->trans('Title') . ' EN',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ])
            ->add("title_hr", TextType::class, [
                'label' => $this->translator->trans('Title') . ' HR',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ])
            ->add("published_at", DateType::class, [
                'label' => $this->translator->trans('Published At'),
                'widget' => 'single_text',
                'input' => 'string',
                'required' => false,
                'empty_data' => date('Y-m-d'),
                'constraints' => [
                    new NotBlank(),
                    new Date(),
                ],
            ])
            ->add("image", FileType::class, [
                'label' => $this->translator->trans('Image'),
                'required' => false,
                'constraints' => [
                    $isUpdate ? new Optional() : new NotBlank(),
                    new File([
                        'maxSize' => '2m',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => $this->translator->trans('Please upload a valid image file.'),
                    ]),
                ],
            ])
            ->add("content_en", TextareaType::class, [
                'label' => $this->translator->trans('Content') . ' EN',
                'required' => true,
                'attr' => ['rows' => 5],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 50000]),
                ],
            ])
            ->add("content_hr", TextareaType::class, [
                'label' => $this->translator->trans('Content') . ' HR',
                'required' => true,
                'attr' => ['rows' => 5],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 50000]),
                ],
            ])
            ->add("tags", ChoiceType::class, [
                'label' => $this->translator->trans('Tags'),
                'required' => false,
                'multiple' => true,
                'data' => isset($options['data']) ? array_column($options['data']['tags'], 'id') : [],
                'choices' => $this->tagRepository->getTagsForSelection(),
                'constraints' => [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
