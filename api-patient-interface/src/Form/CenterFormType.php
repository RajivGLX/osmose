<?php

namespace App\Form;

use App\Entity\Center;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class CenterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];

        $builder
            ->add('name', TextType::class, [
                'label' => $translator->trans('backend.center.label_name'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Indiquez le nom du centre',
                    ]),
                ],
            ])
            ->add('email', TextType::class, [
                'label' => $translator->trans('backend.center.label_email'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Indiquez l\'email du centre',
                    ]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => $translator->trans('backend.center.label_phone'),
                'required' => false,
            ])
            ->add('band', TextType::class, [
                'label' => $translator->trans('backend.center.label_band'),
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => $translator->trans('backend.center.label_address'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Indiquez l\'adresse du centre',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => $translator->trans('backend.center.label_city'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Indiquez la ville du centre',
                    ]),
                ],
            ])
            ->add('zipcode', TextType::class, [
                'label' => $translator->trans('backend.center.label_zipcode'),
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Indiquez le code postal du centre',
                    ]),
                ],
            ])
            ->add('region', EntityType::class, [
                'label' => $translator->trans('backend.center.label_region'),
                'required' => true,
                'mapped' => false,
                'class' => Region::class
            ])
            ->add('url', TextType::class, [
                'label' => $translator->trans('backend.center.label_url'),
                'required' => false,
            ])
            ->add('place_available', IntegerType::class, [
                'label' => $translator->trans('backend.center.label_place_available'),
                'required' => true,
            ])
            ->add('latitude_longitude', TextType::class, [
                'label' => $translator->trans('backend.center.label_Latitude_longitude'),
                'required' => false
            ])
            ->add('active', ChoiceType::class, [
                'label' => $translator->trans('backend.center.label_active'),
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => false,
            ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $center = $event->getData();
            $form = $event->getForm();

            if ($center === null) {
                // If it's null, add the centerSlots field without any data
                $form->add('centerSlots', CollectionType::class, [
                    'entry_type' => CenterSlotFormType::class,
                    'entry_options' => ['label' => false],
                    'allow_delete' => true,
                    'by_reference' => false,
                ]);
            } else {
                // If it's not null, get the first 3 slots
                $slots = $center->getCenterSlots()->slice(0, 3);

                // Add the centerSlots field with the first 3 slots
                $form->add('centerSlots', CollectionType::class, [
                    'entry_type' => CenterSlotFormType::class,
                    'entry_options' => ['label' => false],
                    'allow_delete' => true,
                    'by_reference' => false,
                    'data' => $slots,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('translator');
        $resolver->setDefaults([
            'data_class' => Center::class,
        ]);
    }
}
