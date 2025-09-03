<?php

namespace App\Form;

use App\Entity\Patient;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatientInformationMinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];

        $builder
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone :',
                'required' => true,
                'constraints' => [
                    new Length([
                        'max' => 10,
                        'maxMessage' => 'Le champ numéro de téléphone ne peut pas contenir plus de {{ limit }} chiffres',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]*$/',
                        'message' => 'Le champ  numéro de téléphone ne peut contenir que des chiffres',
                    ]),
                ],
            ])
            ->add('typeDialysis', ChoiceType::class, [
                'label' => 'Type de dialyse :',
                'required' => true,
                'choices' => [
                    'Non renseigné' => 'Non renseigné',
                    'Hémodialyse' => 'Hémodialyse ',
                    'Dialyse péritonéale' => 'Dialyse péritonéale',
                ],
                'placeholder' => false,
                'constraints' => [
                    new NotEqualTo([
                        'value' => 'Non renseigné',
                        'message' => 'Le type de dialyse ne peut pas être "Non renseigné".',
                    ]),
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('translator');
        $resolver->setDefaults([
            'data_class' => Patient::class,
        ]);
    }
}
