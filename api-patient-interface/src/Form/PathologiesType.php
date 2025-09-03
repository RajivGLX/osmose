<?php

namespace App\Form;

use App\Entity\Pathologies;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PathologiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];

        $builder
            ->add('boolHeartDisease', ChoiceType::class, [
                'label' => 'Avez-vous une maladie cardiaque ?',
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'Non renseigné' => null,
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => false,
            ])
            ->add('heartDisease', TextType::class, [
                'label' => 'Si oui, veuillez la préciser :',
                'required' => false,
                'constraints' => [
                    new Callback([
                        'callback' => function($value, ExecutionContextInterface $context) {
                            $form = $context->getRoot();
                            if ($form['patient']['pathologies']['boolHeartDisease']->getData() === true && empty($value)) {
                                $context->buildViolation('Veuillez préciser votre maladie cardiaque')
                                    ->atPath('heartDisease')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ])
            ->add('boolDiabetes', ChoiceType::class, [
                'label' => 'Avez-vous du diabéte ?',
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'Non renseigné' => null,
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => false,
            ])
            ->add('diabetes', TextType::class, [
                'label' => 'Si oui, veuillez la préciser dequel type il sagit :',
                'required' => false,
                'constraints' => [
                    new Callback([
                        'callback' => function($value, ExecutionContextInterface $context) {
                            $form = $context->getRoot();
                            if ($form['patient']['pathologies']['boolDiabetes']->getData() === true && empty($value)) {
                                $context->buildViolation('Veuillez préciser votre diabéte')
                                    ->atPath('diabetes')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ])
            ->add('boolMusculoskeletalProblems', ChoiceType::class, [
                'label' => 'Avez-vous des troubles musculosquelettiques ?',
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'Non renseigné' => null,
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => false,
            ])
            ->add('musculoskeletalProblems', TextType::class, [
                'label' => 'Si oui, veuillez préciser le trouble :',
                'required' => false,
                'constraints' => [
                    new Callback([
                        'callback' => function($value, ExecutionContextInterface $context) {
                            $form = $context->getRoot();
                            if ($form['patient']['pathologies']['boolMusculoskeletalProblems']->getData() === true && empty($value)) {
                                $context->buildViolation('Veuillez préciser le trouble musculosquelettique')
                                    ->atPath('musculoskeletalProblems')
                                    ->addViolation();
                            }
                        },
                    ]),
                ],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('translator');
        $resolver->setDefaults([
            'data_class' => Pathologies::class,
        ]);
    }
}
