<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['current_password_is_required']) {
            $builder
                ->add('currentPassword', PasswordType::class, [
                    'attr' => [
                        'autocomplete' => 'off'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer votre mot de passe.',
                        ]),
                        new UserPassword([
                            'message' => 'Mot de passe actuel invalide.',
                        ]),
                    ],
                    'label' => 'Mot de passe actuel',
                ]);
        }

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un nouveau mot de passe.',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères.',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Confirmer le nouveau mot de passe',
                ],
                'invalid_message' => 'Les champs de mot de passe doivent correspondre.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_password_is_required' => false
        ]);
        $resolver->setAllowedTypes('current_password_is_required', 'bool');
    }
}
