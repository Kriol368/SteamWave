<?php

// src/Form/PostFormType.php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpClient\HttpClient;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Fetch JSON data from the /user/games-list route
        $client = HttpClient::create();
        // This should be replaced when in prod env
        $response = $client->request('GET', 'http://127.0.0.1:8000/user/games-list');

        $gamesData = $response->toArray(); // Get the JSON as an array

        // Extract the names of the games (assuming the second property is the name)
        $gameChoices = [];
        foreach ($gamesData as $game) {
            $gameChoices[$game[1]] = $game[1];  // Using the second property as the label and value
        }

        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Post Content',
                'attr' => [
                    'placeholder' => 'Write your post here...',
                    'rows' => 5,
                ],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'data' => new \DateTime(),
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
                'label' => 'Tag a Game',
                'placeholder' => 'Select a game',
                'choices' => $gameChoices, // Pass the game choices
                'attr' => [
                    'id' => 'tag_select', // Set an ID to access with JavaScript
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
