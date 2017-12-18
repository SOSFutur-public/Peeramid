<?php

namespace AppBundle\Form;

use AppBundle\Constants;
use AppBundle\Entity\AssignmentSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignmentSectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    /** @var AssignmentSection $assignmentSection */
                    $assignmentSection = $event->getData();
                    /** @var Form $form */
                    $form = $event->getForm();
                    if ($assignmentSection->getSection()->getSectionType()->getId() !== Constants::SECTION_TYPE_FILE) {
                        $form->add('answer', TextType::class);
                    }
                }
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\AssignmentSection'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_assignmentsection';
    }
}