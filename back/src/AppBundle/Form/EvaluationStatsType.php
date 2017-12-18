<?php

namespace AppBundle\Form;

use AppBundle\Constants;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationStatsType extends AbstractType
{
    private $teacherId;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->teacherId = $options['teacher'];
        $builder
            ->add('show_assignment_mark', CheckboxType::class)
            ->add('show_corrections_mark', CheckboxType::class)
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
            ))
            ->add('use_teacher_mark', CheckboxType::class)
            ->add('sections', CollectionType::class, array(
                'entry_type' => SectionStatsType::class,
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
