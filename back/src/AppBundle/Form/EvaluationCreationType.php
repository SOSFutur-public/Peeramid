<?php

namespace AppBundle\Form;

use AppBundle\Constants;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationCreationType extends AbstractType
{
    private $teacherId;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->teacherId = $options['teacher'];
        $builder
            ->add('name', TextType::class)
            ->add('subject', TextType::class)
            ->add('lesson', EntityType::class, array(
                    'class' => 'AppBundle\Entity\Lesson',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('l')
                            ->innerJoin('l.users', 'u')
                            ->where('u.id = :id')
                            ->setParameter('id', $this->teacherId);
                    }
                )
            )
            ->add('mark_mode', EntityType::class, array(
                'class' => 'AppBundle\Entity\MarkMode',
                'empty_data' => (string)Constants::EVALUATION_MARK_MODE_DEFAULT
            ))
            ->add('mark_precision_mode', EntityType::class, array(
                'class' => 'AppBundle\Entity\MarkPrecisionMode',
                'empty_data' => (string)Constants::EVALUATION_MARK_PRECISION_MODE_DEFAULT
            ))
            ->add('mark_round_mode', EntityType::class, array(
                'class' => 'AppBundle\Entity\MarkRoundMode',
                'empty_data' => (string)Constants::EVALUATION_MARK_ROUND_MODE_DEFAULT
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Evaluation',
            'teacher' => null
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
