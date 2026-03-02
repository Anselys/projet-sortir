<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false, 'attr' => [
                    'placeholder' => 'Nom du lieu',
                ]
            ])
            ->add('ville', EntityType::class, [
                'mapped' => false,
                'label' => false, 'class' => Ville::class,
                'choice_label' => 'nom',
                'query_builder' => function (VilleRepository $er) {
                    return $er->createQueryBuilder('ville')->orderBy('ville.nom', 'ASC');
                },
                'placeholder' => '-- Sélectionner la ville --',
            ])
            ->add('rue', TextType::class, [
                'label' => false, 'attr' => [
                    'placeholder' => 'Rue',
                ]
            ])
            ->add('latitude', TextType::class, [
                'label' => false, 'attr' => [
                    'placeholder' => 'Latitude (Facultatif)',

                ],
                'required' => false,
            ])
            ->add('longitude', TextType::class, [
                'label' => false, 'attr' => [
                    'placeholder' => 'Longitude (Facultatif)',

                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
