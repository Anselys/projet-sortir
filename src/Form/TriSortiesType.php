<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TriSortiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Site', EntityType::class, [
                'label' => 'Campus',
                'class' => Site::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'TOUS LES SITES',
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')->orderBy('s.nom', 'ASC');
                }
            ])
            ->add('recherche', TextType::class, [
                'label' => 'Rechercher par nom',
                'attr' => [
                    'placeholder' => 'Rechercher...',
                ],
                'required' => false,
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'label' => 'État de la sortie :',
                'required' => false,
                'placeholder' => 'Toutes les sorties',
                'choice_label' => function (Etat $etat) {
                    return match ($etat->getLibelle()) {
                        'OUVERTE' => 'Ouverte',
                        'ANNULEE' => 'Annulée',
                        'CLOTUREE' => 'Inscriptions clôturées',
                        'EN_COURS' => 'En cours',
                        'PASSEE' => 'Terminée',
                        'CREEE' => 'Créée',
                        default => $etat->getLibelle(),
                    };
                },
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')->orderBy('s.libelle', 'ASC');
                },
            ])
            ->add('dateDebutDe', DateType::class,[
                'label' => "Entre ",
                'required' => false,
            ])
            ->add('dateDebutA', DateType::class,[
                'label' => " et ",
                'required' => false,
            ])
            ->add('organisateur', CheckboxType::class, [
                'label'=> 'Sorties dont je suis l\'organisateur·ice',
                'required' => false,
            ])
            ->add('inscrit', CheckboxType::class, [
                'label'=> 'Sorties auxquelles je suis inscrit·e',
                'required' => false,
            ])
            ->add('non_inscrit', CheckboxType::class, [
                'label'=> 'Sorties auxquelles je ne suis pas inscrit·e',
                'required' => false,
            ])
            ->add('passees', CheckboxType::class, [
                'label'=> 'Sorties passées',
                'required' => false,
            ])
            ->add('Submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
//            ->add('Reset', ResetType::class, [
//                'label' => 'Réinitialiser',
//                'attr' => [
//                    'class' => 'btn btn-primary',
//                    ]
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
