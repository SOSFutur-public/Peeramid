<?php

namespace AppBundle\Form;

use AppBundle\Constants;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('lessons', CollectionType::class, array(
                'entry_type' => EntityType::class,
                'entry_options' => array(
                    'class' => 'AppBundle\Entity\Lesson'
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('users', CollectionType::class, array(
                'entry_type' => EntityType::class,
                'entry_options' => array(
                    'class' => 'AppBundle\Entity\User',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('u')
                            ->innerJoin('u.role', 'r')
                            ->where('r.id = :id')
                            ->setParameter('id', Constants::ROLE_STUDENT);
                    }
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
            'data_class' => 'AppBundle\Entity\Group'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_group';
    }


}
