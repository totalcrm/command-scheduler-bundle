<?php

namespace TotalCRM\CommandSchedulerBundle\Form\Type;

use TotalCRM\CommandSchedulerBundle\Service\CommandParser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommandChoiceType.
 */
class CommandChoiceType extends AbstractType
{
    /**
     * @var CommandParser
     */
    private $commandParser;

    /**
     * @param CommandParser $commandParser
     */
    public function __construct(CommandParser $commandParser)
    {
        $this->commandParser = $commandParser;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => $this->commandParser->getCommands(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
