<?php

namespace App\Form;

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];
        $data = $options['data'] ?? null;
        $password = $data !== null ? $options['data']->getPassword() : null;

        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ email ne doit pas être vide',
                    ]),
                    new Email([
                        'message' => 'Entrez un email valide',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ prénom ne doit pas être vide',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le champ prénom doit contenir au moins 2 caractéres',
                        'maxMessage' => 'Le champ prénom doit contenir moins de 30 caractéres'
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'match' => false,
                        'message' => 'Le champ prénom ne peut pas contenir de chiffres',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de famille :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ nom ne doit pas être vide',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le champ nom doit contenir au moins 2 caractéres',
                        'maxMessage' => 'Le champ nom   doit contenir moins de 30 caractéres'
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'match' => false,
                        'message' => 'Le champ nom de famille ne peut pas contenir de chiffres',
                    ]),
                ],
            ])
            ->add('password', TextType::class, [
                'required' => 'required',
                'mapped' => false,
                'label' => 'Mot de passe',
                'data' => $password,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ mot de passe ne doit pas être vide',
                    ]),
                ],
            ])
            ->add('patient', PatientType::class, [
                'required' => false,
                'translator' => $translator,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('translator');
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
