<?php

namespace TotalCRM\CommandScheduler\Controller;

use TotalCRM\CommandScheduler\Entity\ScheduledCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListController.
 */
class ListController extends BaseController
{
    /**
     * @var string
     */
    private $lockTimeout;

    /**
     * @param $lockTimeout string
     */
    public function setLockTimeout($lockTimeout)
    {
        $this->lockTimeout = $lockTimeout;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction(): JsonResponse
    {
        $scheduledCommands = $this->getDoctrineManager()->getRepository('CommandSchedulerBundle:ScheduledCommand')->findAll();

        $response = new JsonResponse();
        $response->setContent(json_encode($scheduledCommands));

        return $response;
    }

    /**
     * @param $id
     * @return Response
     */
    public function removeAction($id)
    {
        $entityManager = $this->getDoctrineManager();
        $scheduledCommand = $entityManager->getRepository(ScheduledCommand::class)->find($id);

        $entityManager->remove($scheduledCommand);
        $entityManager->flush();

        // Add a flash message and do a redirect to the list
        $this->get('session')->getFlashBag()->add('success', $this->translator->trans('flash.deleted', [], 'CommandScheduler'));

        return $this->redirect($this->generateUrl('totalcrm_command_scheduler_list'));
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function toggleAction($id)
    {
        $entityManager = $this->getDoctrineManager();
        $scheduledCommand = $entityManager->getRepository(ScheduledCommand::class)->find($id);
        $scheduledCommand->setDisabled(!$scheduledCommand->isDisabled());
        $entityManager->flush();

        return $this->redirect($this->generateUrl('totalcrm_command_scheduler_list'));
    }

    /**
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executeAction($id, Request $request)
    {
        $entityManager = $this->getDoctrineManager();
        $scheduledCommand = $entityManager->getRepository(ScheduledCommand::class)->find($id);
        $scheduledCommand->setExecuteImmediately(true);
        $entityManager->flush();

        // Add a flash message and do a redirect to the list
        $this->get('session')->getFlashBag()
            ->add('success', $this->translator->trans('flash.execute', [], 'CommandScheduler'));

        if ($request->query->has('referer')) {
            return $this->redirect($request->getSchemeAndHttpHost().urldecode($request->query->get('referer')));
        }

        return $this->redirect($this->generateUrl('totalcrm_command_scheduler_list'));
    }

    /**
     * @param $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unlockAction($id, Request $request)
    {
        $entityManager = $this->getDoctrineManager();
        $scheduledCommand = $entityManager->getRepository(ScheduledCommand::class)->find($id);
        $scheduledCommand->setLocked(false);
        $entityManager->flush();

        // Add a flash message and do a redirect to the list
        $this->get('session')->getFlashBag()
            ->add('success', $this->translator->trans('flash.unlocked', [], 'CommandScheduler'));

        if ($request->query->has('referer')) {
            return $this->redirect($request->getSchemeAndHttpHost().urldecode($request->query->get('referer')));
        }

        return $this->redirect($this->generateUrl('totalcrm_command_scheduler_list'));
    }

    /**
     * @return JsonResponse
     */
    public function monitorAction()
    {
        $failedCommands = $this->getDoctrineManager()
            ->getRepository(ScheduledCommand::class)
            ->findFailedAndTimeoutCommands($this->lockTimeout);

        $jsonArray = [];
        foreach ($failedCommands as $command) {
            $jsonArray[$command->getName()] = [
                'LAST_RETURN_CODE' => $command->getLastReturnCode(),
                'B_LOCKED' => $command->getLocked() ? 'true' : 'false',
                'DH_LAST_EXECUTION' => $command->getLastExecution(),
            ];
        }

        $response = new JsonResponse();
        $response->setContent(json_encode($jsonArray));
        $response->setStatusCode(count($jsonArray) > 0 ? Response::HTTP_EXPECTATION_FAILED : Response::HTTP_OK);

        return $response;
    }
}
