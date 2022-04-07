<?php

namespace TotalCRM\CommandSchedulerBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\TranslatorInterface as ComponentTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as ContractsTranslatorInterface;

/**
 * Class BaseController.
 */
abstract class BaseController extends AbstractController
{
    /**
     * @var string
     */
    private $managerName;

    /**
     * @var ContractsTranslatorInterface|ComponentTranslatorInterface
     */
    protected $translator;

    /**
     * @param $managerName string
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;
    }

    /**
     * @param ContractsTranslatorInterface|ComponentTranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return ObjectManager
     */
    protected function getDoctrineManager()
    {
        return $this->getDoctrine()->getManager($this->managerName);
    }
}
