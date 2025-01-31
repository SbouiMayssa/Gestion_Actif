<?php

namespace App\Form;

use App\Entity\Actif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Employer;

class ActifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le nom de l’actif', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom doit avoir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('type', TextType::class, [
                'label' => 'Type',
                'attr' => ['placeholder' => 'Ordinateur, Imprimante...', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type est obligatoire'])
                ]
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État',
                'placeholder' => 'Sélectionnez un état',
                'choices' => [
                    'Fonctionnel' => 'fonctionnel',
                    'En panne' => 'en panne',
                    'Remplacé' => 'remplacé'
                ],
                'expanded' => false, 
                'multiple' => false,
                'attr' => [
                    'class' => 'form-select', 
                    'aria-label' => 'Sélectionnez un état'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’état est obligatoire'])
                ]
            ])
            ->add('dateAcquisation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'Acquisation',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez entrer une date']),
                    new Assert\LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date d’acquisation ne peut pas être dans le futur'
                    ])
                ]
            ])
            ->add('numSerie', TextType::class, [
                'label' => 'Numéro de Série',
                'attr' => ['placeholder' => '123ABC456', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de série est obligatoire']),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 20,
                        'minMessage' => 'Le numéro de série doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le numéro de série ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])


            ->add('userAssigned', EntityType::class, [
                'class' => Employer::class,
                'choice_label' => function(Employer $employer) {
                    return $employer->getPrenom() . ' ' . $employer->getNom();},
                'multiple' => true,
                'expanded' => false, 
                'label' => 'Employés assignés',
                'attr' => ['class' => 'form-control select2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Actif::class,
        ]);
    }
}
