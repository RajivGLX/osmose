<?php

namespace App\Form;

use App\Entity\Center;
use App\Entity\Patient;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];

        $builder
            ->add('checked', ChoiceType::class, [
                'label' => 'Adresse mail confirmée :',
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => false,
            ])
            ->add('medicalHistory', TextareaType::class, [
                'label' => 'Information médical spécifique :',
                'required' => false,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone :',
                'required' => false,
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
            ->add('dialysisStartDate', DateType::class, [
                'label' => 'Quelle est la date à laquelle vous avez débuté la dialyse ?',
                'required' => false,
                'widget' => 'single_text',
//                'constraints' => [
//                    new Date([
//                        'message' => 'La valeur {{ value }} n\'est pas une date valide.',
//                    ]),
//                ],
            ])
            ->add('typeDialysis', ChoiceType::class, [
                'label' => 'Type de dialyse :',
                'required' => false,
                'choices' => [
                    'Non renseigné' => null,
                    'Hémodialyse' => 'Hémodialyse ',
                    'Dialyse péritonéale' => 'Dialyse péritonéale',
                ],
                'placeholder' => false,
            ])
            ->add('renalFailure', ChoiceType::class, [
                'label' => 'Type d\'insuffisance rénale :',
                'required' => false,
                'choices' => [
                    'Non renseigné' => null,
                    'Diabète' => 'Diabète ',
                    'Hypertension artérielle' => 'Hypertension artérielle',
                    'Glomérulonéphrite' => 'Glomérulonéphrite ',
                    'Maladie polykystique des reins' => 'Maladie polykystique des reins',
                    'Autre (veuillez préciser)' => 'Autre ',
                ],
                'placeholder' => false,
            ])
            ->add('drugAllergies', ChoiceType::class, [
                'label' => 'Avez-vous des allergies aux médicaments ?',
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'Non renseigné' => null,
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => false,
            ])
            ->add('drugAllergiePrecise', TextType::class, [
                'label' => 'Si oui, veuillez la/les préciser :',
                'required' => false,
                'constraints' => [
                    new Callback([
                        'callback' => function($value, ExecutionContextInterface $context) {
                            $form = $context->getRoot();
                            if ($form['patient']['drugAllergies']->getData() === true && empty($value)) {
                                $context->buildViolation('Veuillez préciser vos allergies aux médicaments')
                                    ->atPath('drug_allergie_precise')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ])
            ->add('vascularAccessType', ChoiceType::class, [
                'label' => 'Informations sur l\'accès vasculaires',
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'Non renseigné' => null,
                    'Fistule artérioveineuse (FAV)' => 'FAV',
                    'Greffe de ponction veineuse (GPV)' => 'GPV',
                    'Cathéter veineux central (CVC)' => 'CVC',
                ],
                'placeholder' => false,
            ])
            ->add('center', EntityType::class, [
                'class' => Center::class,
                'required' => false,
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
