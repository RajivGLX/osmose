<?php

namespace App\Form;

use App\Entity\Administrator;
use App\Entity\Center;
use App\Entity\Role;
use App\Entity\User;
use App\Services\Identifier;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TranslatorInterface $translator */
        $translator = $options['translator'];
        $userAdmin = $this->security->getUser()->getAdministrator();
        $builder
            ->add('firstname', TextType::class, ['label' => $translator->trans('backend.user.firstname')])
            ->add('lastname', TextType::class, ['label' => $translator->trans('backend.user.lastname')])
            ->add('email', EmailType::class)
            ->add('password', TextType::class, [
                'label' => $translator->trans('backend.user.password'),
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => $translator->trans('backend.global.must_not_be_empty')]),
                ],
            ])
            ->add('role', EntityType::class, [
                'mapped' => false,
                'class' => Role::class,
                'required' => false,
                'label' => $translator->trans('backend.user.label_role'),
                'placeholder' => $translator->trans('backend.role.choice_role'),
            ])
            ->add('center', EntityType::class, [
                'mapped' => false,
                'class' => Center::class,
                'query_builder' => function (EntityRepository $er) use ($userAdmin) {
                    $user = $this->security->getUser()->getRoles();
                    if ($user[0] == 'ROLE_ADMIN_OSMOSE'){
                        $qb = $er->createQueryBuilder('c');
                        $qb->select('c');
                        return $qb;
                     }else{
                        $qb = $er->createQueryBuilder('u');
                        $qb->innerJoin('u.administrator', 'a');
                        $qb->innerJoin('a.centers', 'c');
                        $qb->where('a.id = :adminId');
                        $qb->setParameter('adminId', $userAdmin->getId());
                        return $qb;
                    }
                },
                'required' => true,
                'placeholder' => $translator->trans('backend.center.choice_center'),
                'multiple' => true,
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
