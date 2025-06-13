<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class)
            ->add('modele', TextType::class)
            ->add('immatriculation', TextType::class)
            ->add('prixParJour', NumberType::class)
            ->add('couleur', TextType::class)
            ->add('poids', NumberType::class)
            ->add('disponible', CheckboxType::class, [
                'required' => false,
            ])
            ->add('dateAjout', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            // 📸 Foto principal
            ->add('photo', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image principale',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Merci de sélectionner une image valide',
                    ])
                ]
            ])
            // 🖼️ Fotos adicionales
            ->add('photos', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Photos supplémentaires',
                'multiple' => true,
                'constraints' => [
                  new All([  
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Merci de sélectionner uniquement des images valides',
                    ])
                ]
                              ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}
