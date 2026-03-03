<?php

namespace App\Form;


use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    private LieuRepository $lieuRepository;

    public function __construct(LieuRepository $lieuRepository)
    {
        $this->lieuRepository = $lieuRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
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
                'label' => 'Date limite d\'inscription',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => [
                    'placeholder' => 'Nombre de places',
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description et infos',
                'attr' => [
                    'placeholder' => 'Description de la sortie',
                ],
            ])
            ->add('ville', EntityType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'class' => Ville::class,
                'choice_label' => function (Ville $ville) {
                    return $ville->getNom() . ' - ' . $ville->getCpo();
                },
                'placeholder' => '-- Sélectionner la ville --',
                'data' => $options['data']->getLieu()?->getVille(),
            ])

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $sortie = $event->getData();
                $form = $event->getForm();

                $ville = $sortie->getLieu()?->getVille();

                $lieux = $ville
                    ? $this->lieuRepository->findBy(['ville' => $ville])
                    : [];

                $form->add('lieu', EntityType::class, [
                    'class' => Lieu::class,
                    'choices' => $lieux,
                    'choice_label' => function (Lieu $lieu) {
                        return $lieu->getNom();
                    },
                    'placeholder' => '-- Choisir un lieu --',
                    'data' => $sortie->getLieu(),
                ]);
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

                $data = $event->getData();
                $form = $event->getForm();

                if (!isset($data['ville']) || empty($data['ville'])) {
                    return;
                }

                $villeId = $data['ville'];

                $lieux = $this->lieuRepository->findBy(['ville' => $villeId]);

                $form->add('lieu', EntityType::class, [
                    'class' => Lieu::class,
                    'choices' => $lieux,
                    'choice_label' => function (Lieu $lieu) {
                        return $lieu->getNom();
                    },
                    'placeholder' => '-- Choisir un lieu --',

                ]);
            })

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $etat = $event->getData()->getEtat();
                $form = $event->getForm();
                $defaultValue = true;

                if ($etat && $etat->getLibelle() == 'CREEE') {
                    $defaultValue = false;
                }

                $form->add('publier', CheckboxType::class, [
                    'mapped' => false,
                    'label' => 'Publier la sortie',
                    'data' => $defaultValue,
                    'required' => false,
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
