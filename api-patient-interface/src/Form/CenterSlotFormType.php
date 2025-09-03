<?php

namespace App\Form;

use App\Entity\CenterSlot;
use App\Entity\Slots;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CenterSlotFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('slot', EntityType::class, [
                'class' => Slots::class,
                'choice_label' => 'name',
                'label' => 'Slot',
            ])
            ->add('hour_start', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('hour_end', TimeType::class, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CenterSlot::class,
        ]);
    }
}