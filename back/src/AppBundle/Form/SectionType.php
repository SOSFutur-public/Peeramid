<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('subject', TextType::class)
            ->add('order', IntegerType::class)
            ->add('section_type', EntityType::class, array('class' => 'AppBundle\Entity\SectionType'))
            ->add('max_size', IntegerType::class)
            ->add('limit_file_types', CheckboxType::class)
            ->add('file_types', CollectionType::class, array(
                    'entry_type' => EntityType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_options' => array(
                        'class' => 'AppBundle\Entity\FileType'
                    )
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Section'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_section';
    }
}
