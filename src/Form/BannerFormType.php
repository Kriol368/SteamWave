<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BannerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('banner', ChoiceType::class, [
                'label' => 'Banner',
                'choices' => [],
                'attr' => ['id' => 'banner-select'],
                'mapped' => true,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $submittedData = $event->getData();

            $banner = $submittedData['banner'] ?? null;

            if ($banner) {
                $form->add('banner', ChoiceType::class, [
                    'choices' => [$banner => $banner],
                    'attr' => ['id' => 'banner-select'],
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                ]);
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'games' => [], // Default empty array
        ]);
    }
}
