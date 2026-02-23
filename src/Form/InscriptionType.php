<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Nom d\'utilisateur',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('prenom',TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('email')
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $er) {
                    return $er->createQueryBuilder('site')->orderBy('site.nom', 'ASC');
                },
                'placeholder' => ' -- Choisissez votre campus --',
            ])
            ->add('telephone',TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'mapped' => false,
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
                    'label' => 'Mot de passe'
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe'
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
