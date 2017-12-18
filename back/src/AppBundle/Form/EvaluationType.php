<?php

namespace AppBundle\Form;

use AppBundle\Constants;
use AppBundle\Form\EventSubscriber\SortSectionsSubscriber;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationType extends AbstractType
{
    private $lessonId;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->lessonId = $options['lesson'];
        $builder
            ->addEventSubscriber(new SortSectionsSubscriber())
            ->add('name', TextType::class)
            ->add('subject', TextType::class)
            ->add('date_start_assignment', DateTimeType::class, array(
                'widget' => 'single_text'
            ))
            ->add('date_end_assignment', DateTimeType::class, array(
                'widget' => 'single_text'
            ))
            ->add('individual_assignment', CheckboxType::class)
            ->add('assignment_instructions', TextType::class)
            ->add('users', CollectionType::class, array(
                'entry_type' => EntityType::class,
                'entry_options' => array(
                    'class' => 'AppBundle\Entity\User',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('u')
                            ->innerJoin('u.role', 'r')
                            ->where('r.id = :id')
                            ->innerJoin('u.lessons', 'l')
                            ->andWhere('l.id = :lessonId')
                            ->setParameter('id', Constants::ROLE_STUDENT)
                            ->setParameter('lessonId', $this->lessonId);
                    }
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('groups', CollectionType::class, array(
                'entry_type' => EntityType::class,
                'entry_options' => array(
                    'class' => 'AppBundle\Entity\Group',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('g')
                            ->innerJoin('g.lessons', 'l')
                            ->andWhere('l.id = :lessonId')
                            ->setParameter('lessonId', $this->lessonId);
                    }
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('sections', CollectionType::class, array(
                'entry_type' => SectionType::class,
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
            'data_class' => 'AppBundle\Entity\Evaluation',
            'lesson' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_evaluation';
    }
}