<?php

namespace App\Form;

use App\Entity\Chat;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class ChatFormType extends AbstractType
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $this->security->getUser(); // Get the currently logged-in user

        // Get the list of users, excluding the current user
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $builder->add('users', EntityType::class, [
            'class' => User::class,
            'choices' => $users,
            'multiple' => true,
            'expanded' => true,
            'data' => [$currentUser],
            'choice_label' => function(User $user) {
                return $user->getName(); // Assuming 'name' is a method on the User entity
            },
            'attr' => ['class' => 'user-selection'],
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chat::class,
        ]);
    }
}
