<?php

namespace AppBundle\Form;

use AppBundle\Form\EventSubscriber\SortSectionsSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationCorrectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new SortSectionsSubscriber())
            ->add('date_start_correction', DateTimeType::class, array(
                'widget' => 'single_text'
            ))
            ->add('date_end_correction', DateTimeType::class, array(
                'widget' => 'single_text'
            ))
            ->add('date_end_opinion', DateTimeType::class, array(
                'widget' => 'single_text'
            ))
            ->add('number_corrections', IntegerType::class)
            ->add('anonymity', CheckboxType::class)
            ->add('individual_correction', CheckboxType::class)
            ->add('correction_instructions', TextType::class)
            ->add('sections', CollectionType::class, array(
                'entry_type' => SectionCorrectionType::class,
                'by_reference' => false
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Evaluation'
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
