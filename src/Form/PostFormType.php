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
                'label' => 'Contenido del Post',
                'attr' => [
                    'placeholder' => 'Escribe tu publicación aquí...',
                    'rows' => 5,
                    'class' => 'form-control-content', // Clase para estilo
                ],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'data' => new DateTime(),
                'widget' => 'single_text',
                'label' => false, // Ocultar la etiqueta
                'html5' => true,
                'attr' => [
                    'style' => 'display:none;',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Attach Image or Video (Optional)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file',
                    'accept' => 'image/*,video/*',
                ],
            ])
            ->add('numLikes', HiddenType::class, [
                'data' => 0,
            ])
            ->add('tag', ChoiceType::class, [
                'label' => 'Etiqueta',
                'choices' => [],
                'attr' => [
                    'id' => 'tag-select',
                    'class' => 'form-control-tag', // Clase para el select
                ],
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
                    'choices' => [$tag => $tag], // Asegurar que el tag se mantenga
                    'attr' => [
                        'id' => 'tag-select',
                        'class' => 'form-control-tag', // Clase para el select
                    ],
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
