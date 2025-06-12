<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => ['rows' => 5, 'placeholder' => 'Écrivez ici votre avis...'],
                'required' => true,
            ])
            ->add('note', IntegerType::class, [
                'label' => 'Note (0 à 5)',
                'required' => true,
                'constraints' => [
                    new Range(['min' => 0, 'max' => 5, 'notInRangeMessage' => 'La note doit être entre 0 et 5.']),
                ],
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
