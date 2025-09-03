<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];

        $builder
            ->add('current_password', PasswordType::class, [
                'label' => $translator->trans('backend.user.current_password'),
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => $translator->trans('backend.global.must_not_be_empty')]),
                    new UserPassword(['message' => $translator->trans('backend.user.error_current_password')]),
                ],
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => $translator->trans('backend.user.new_password_must_be'),
                'required' => 'required',
                'first_options' => ['label' => $translator->trans('backend.user.new_password')],
                'second_options' => ['label' => $translator->trans('backend.user.confirm_password')],
                'constraints' => [
                    new NotBlank(['message' => $translator->trans('backend.global.must_not_be_empty')]),
                    new Regex(
                        '"^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"',
                        "Le mot de passe doit comporter au minimum huit caractères, au moins une lettre, un chiffre et un caractère spécial"
                    ),
                ],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('translator');

        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
