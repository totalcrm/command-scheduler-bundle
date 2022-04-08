<?php

namespace TotalCRM\CommandScheduler\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @param $managerName string
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;
    }

    /**
     * @return ObjectManager
     */
    protected function getDoctrineManager()
    {
        return $this->getDoctrine()->getManager($this->managerName);
    }
}
