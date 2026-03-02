<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu',
                'attr' => [
                    'placeholder' => 'Nom du lieu',
                ]
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'attr' => [
                    'placeholder' => 'Nom de la nouvelle ville',
                ]
            ])
//            ->add('latitude')
//            ->add('longitude')
            ->add('ville', EntityType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'class' => Ville::class,
                'choice_label' => 'Ville',
                'query_builder' => function (VilleRepository $er) {
                    return $er->createQueryBuilder('ville')->orderBy('ville.nom', 'ASC');
                },
                'placeholder' => '-- Sélectionner la ville --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
