<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriteriaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class)
            ->add('order', IntegerType::class)
            ->add('mark_max', NumberType::class)
            ->add('mark_min', NumberType::class)
            ->add('precision', NumberType::class)
            ->add('criteria_type', EntityType::class, array('class' => 'AppBundle\Entity\CriteriaType'))
            ->add('criteria_choices', CollectionType::class, array(
                'entry_type' => CriteriaChoiceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('weight', IntegerType::class, array('empty_data' => '1'));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Criteria'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_criteria';
    }
}