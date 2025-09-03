<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Center;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', TextareaType::class,[
                'label' => 'Lasissez un message si votre demande comporte des spécificitées',
                'attr' => ['placeholder' => 'Votre message...','rows' => 5, 'cols' => 25],
                'required' => false,
            ]);

            if ($options['remove_reason']) {
                $builder->add('reason', TextType::class, [
                    'required' => false, 
                ]);
            }else{
                $builder->add('reason', TextType::class, [
                'label' => 'Raison de la demande',
                'attr' => ['placeholder' => 'Vacances, déplacement médical...'],
                'required' => true,
                ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
            'remove_reason' => false,
        ]);
    }
}
