<?php

namespace TotalCRM\CommandSchedulerBundle\Form\Type;

use TotalCRM\CommandSchedulerBundle\Entity\ScheduledCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ScheduledCommandType.
 */
class ScheduledCommandType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', HiddenType::class);

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'detail.name',
                'required' => true,
            ]
        );

        $builder->add(
            'command',
            CommandChoiceType::class,
            [
                'label' => 'detail.command',
                'required' => true,
            ]
        );

        $builder->add(
            'arguments',
            TextType::class,
            [
                'label' => 'detail.arguments',
                'required' => false,
            ]
        );

        $builder->add(
            'cronExpression',
            TextType::class,
            [
                'label' => 'detail.cronExpression',
                'required' => true,
            ]
        );

        $builder->add(
            'logFile',
            TextType::class,
            [
                'label' => 'detail.logFile',
                'required' => false,
            ]
        );

        $builder->add(
            'priority',
            IntegerType::class,
            [
                'label' => 'detail.priority',
                'empty_data' => 0,
                'required' => false,
            ]
        );

        $builder->add(
            'executeImmediately',
            CheckboxType::class,
            [
                'label' => 'detail.executeImmediately',
                'required' => false,
            ]
        );

        $builder->add(
            'disabled',
            CheckboxType::class,
            [
                'label' => 'detail.disabled',
                'required' => false,
            ]
        );

        $builder->add(
            'save',
            SubmitType::class,
            [
                'label' => 'action.save',
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ScheduledCommand::class,
                'wrapper_attr' => 'default_wrapper',
                'translation_domain' => 'TotalCRMCommandScheduler',
            ]
        );
    }

    /**
     * Fields prefix.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'command_scheduler_detail';
    }
}
