<?php

namespace App\Form;

use App\Entity\Availability;
use App\Entity\Booking;
use App\Entity\Center;
use App\Entity\Patient;
use App\Entity\Slots;
use App\Entity\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statusBookings', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }
}
