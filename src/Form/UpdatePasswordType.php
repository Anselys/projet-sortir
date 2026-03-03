<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
            ])
            ->add('newPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'mapped' => true,
                'first_options' => [
                    'constraints' => [
                        new NotBlank(
                            message: 'Mot de passe obligatoire',
                        ),
                        new PasswordStrength(
                            minScore: PasswordStrength::STRENGTH_WEAK,
                            //minScore: PasswordStrength::STRENGTH_MEDIUM,
                            message: 'Veuillez choisir un mdp plus balèze'
                        ),
                        /**
                        new NotCompromisedPassword(
                        message: 'Your password is not compromised',
                        )
                         **/
                    ],
                    'label' => 'Nouveau mot de passe'
                ],
                'second_options' => [
                    'label' => 'Confirmation du nouveau mot de passe'
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
