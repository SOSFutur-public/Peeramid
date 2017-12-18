<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('code', TextType::class)
            ->add('email', EmailType::class)
            ->add('last_name', TextType::class)
            ->add('first_name', TextType::class)
            ->add('role', EntityType::class, array(
                'class' => 'AppBundle\Entity\Role'
            ))
            ->add('lessons', CollectionType::class, array(
                'entry_type' => EntityType::class,
                'entry_options' => array(
                    'class' => 'AppBundle\Entity\Lesson'
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('groups', CollectionType::class, array(
                'entry_type' => EntityType::class,
                'entry_options' => array(
                    'class' => 'AppBundle\Entity\Group'
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'allow_extra_fields' => true,
            'csrf_protection' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }
}
