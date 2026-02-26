<?php

namespace App\Form;


use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => [
                    'placeholder' => 'Nom de la sortie',
                ],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'placeholder' => 'Durée (en minutes)',
                ]
            ])
            ->add('dateCloture', DateTimeType::class, [
                'label' => 'Date limite d\'inscription'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => [
                    'placeholder' => 'Nombre de places',
                    'value' => 10
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description et infos',
                'attr' => [
                    'placeholder' => 'Description de la sortie',
                ],
            ])
//            ->add('urlPhoto')
//            ->add('lieu', ChoiceType::class, [
//                'class' => Lieu::class,
//                'choice_label' => 'id',
//            ])
            ->add('siteOrganisateur', EntityType::class, [
                'label' => 'Ville organisatrice',
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $er) {
                    return $er->createQueryBuilder('site')->orderBy('site.nom', 'ASC');
                },
                'placeholder' => '-- Sélectionner le site --',
            ])
//            ->add('ville', EntityType::class, [
//                'mapped' => false,
//                'label' => 'Ville',
//                'class' => Ville::class,
//                'choice_label' => 'nom',
//                'query_builder' => function (VilleRepository $er) {
//                    return $er->createQueryBuilder('ville')->orderBy('ville.nom', 'ASC');
//                },
//                'placeholder' => '-- Sélectionner la ville --',
//            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function (LieuRepository $er) {
                    return $er->createQueryBuilder('lieu')->orderBy('lieu.nom', 'ASC');
                },
                'placeholder' => '-- Sélectionner le lieu --',
                ])



//            ->add('participants', ChoiceType::class, [
//                'class' => Participant::class,
//                'choice_label' => 'id',
//                'multiple' => true,
//            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
