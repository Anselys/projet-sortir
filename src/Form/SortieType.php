<?php

namespace App\Form;


use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date et heure de la sortie'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée'
            ])
            ->add('dateCloture', DateType::class, [
                'label' => 'Date limite d\'inscription'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('description', TextType::class, [
                'label' => 'Description et infos',
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
                'placeholder' => '-- Sélectionner la ville --',
            ])
//            ->add('participants', ChoiceType::class, [
//                'class' => Participant::class,
//                'choice_label' => 'id',
//                'multiple' => true,
//            ])
            ->add('submit' , SubmitType::class, [
                'label' => 'Créer la sortie',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
