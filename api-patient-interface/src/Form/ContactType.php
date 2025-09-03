<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];

        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Votre message :',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Tapez votre message ici...',
                    'rows' => 5,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tapez votre message',
//                        'message' => $translator->trans('backend.user.current_password'),
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 3000,
                        'minMessage' => 'Votre message doit contenir au moins 5 caractéres',
                        'maxMessage' => 'Votre message doit contenir moins de 300 caractéres'
                    ])
                ],

            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tapez votre email',
                    ]),
                    new Email([
                        'message' => 'Entrez un email valide',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Votre prénom :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tapez votre prénom',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Votre prénom doit contenir au moins 2 caractéres',
                        'maxMessage' => 'Votre prénom doit contenir moins de 30 caractéres'
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'match' => false,
                        'message' => 'Votre prénom ne peut pas contenir de chiffres',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Votre nom de famille :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tapez votre nom',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Votre nom doit contenir au moins 2 caractéres',
                        'maxMessage' => 'Votre nom   doit contenir moins de 30 caractéres'
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'match' => false,
                        'message' => 'Votre prénom ne peut pas contenir de chiffres',
                    ]),
                ],
            ])
            ->add('object', TextType::class, [
                'label' => 'Objet de votre message :',
                'attr' => [
                    'placeholder' => 'Tapez votre objet ici...',
                ],
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Tapez  l\'objet de votre message',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 40,
                        'minMessage' => 'Votre objet doit contenir au moins 2 caractéres',
                        'maxMessage' => 'Votre objet  doit contenir moins de 40 caractéres'
                    ])
                ],
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('translator');
    }
}
