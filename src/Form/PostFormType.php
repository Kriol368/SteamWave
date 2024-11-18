<?php

// src/Form/PostFormType.php

namespace App\Form;

use App\Entity\Post;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Post Content',
                'attr' => [
                    'placeholder' => 'Write your post here...',
                    'rows' => 5,
                ],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'data' => new DateTime(),
                'widget' => 'single_text',
                'label' => false,
                'html5' => true,
                'attr' => [
                    'style' => 'display:none;',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Attach Image or Video (Optional)',
                'required' => false,
                'mapped' => false,
            ])
            ->add('numLikes', HiddenType::class, [
                'data' => 0,
            ])
            ->add('tag', ChoiceType::class, [
                'label' => 'Tag',
                'choices' => [],
                'attr' => ['id' => 'tag-select'],
                'mapped' => true,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $submittedData = $event->getData();

            $tag = $submittedData['tag'] ?? null;

            if ($tag) {
                $form->add('tag', ChoiceType::class, [
                    'choices' => [$tag => $tag], // Ensure $tag is used here
                    'attr' => ['id' => 'tag-select'],
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
