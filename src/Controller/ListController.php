<?php

namespace TotalCRM\CommandScheduler\Controller;

use TotalCRM\CommandScheduler\Entity\Repository\ScheduledCommandRepository;
use TotalCRM\CommandScheduler\Entity\ScheduledCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

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
        /** @var ScheduledCommandRepository $scheduledCommandsRepository */
        $scheduledCommandsRepository = $this->getDoctrineManager()->getRepository(ScheduledCommand::class);
        /** @var ScheduledCommand[] $scheduledCommands */
        $scheduledCommands = $scheduledCommandsRepository->findAll();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $defaultTimezone = (new \DateTime())->getTimezone();

        $scheduledCommandsNormalize = $serializer->normalize($scheduledCommands, null, [
            DateTimeNormalizer::TIMEZONE_KEY => $defaultTimezone
        ]);

        $response = new JsonResponse();
        $response->setContent(json_encode($scheduledCommandsNormalize));

        return $response;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function removeAction($id)
    {
        $entityManager = $this->getDoctrineManager();
        $scheduledCommand = $entityManager->getRepository(ScheduledCommand::class)->find($id);
        $entityManager->remove($scheduledCommand);
        $entityManager->flush();

        $response = new JsonResponse();
        $response->setContent(null);

        return $response;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function toggleAction($id)
    {
        $em = $this->getDoctrineManager();
        $scheduledCommand = $em->getRepository(ScheduledCommand::class)->find($id);
        $scheduledCommand->setDisabled(!$scheduledCommand->isDisabled());
        $em->flush();

        $response = new JsonResponse();
        $response->setContent(null);

        return $response;
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function executeAction($id, Request $request)
    {
        $em = $this->getDoctrineManager();
        $scheduledCommand = $em->getRepository(ScheduledCommand::class)->find($id);
        $scheduledCommand->setExecuteImmediately(true);
        $em->flush();

        $response = new JsonResponse();
        $response->setContent(null);

        return $response;
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function unlockAction($id, Request $request)
    {
        $em = $this->getDoctrineManager();
        $scheduledCommand = $em->getRepository(ScheduledCommand::class)->find($id);
        $scheduledCommand->setLocked(false);
        $em->persist($scheduledCommand);
        $em->flush();

        $response = new JsonResponse();
        $response->setContent(null);

        return $response;
    }

    /**
     * @return JsonResponse
     */
    public function monitorAction()
    {
        $em = $this->getDoctrineManager();
        $failedCommands = $em->getRepository(ScheduledCommand::class)->findFailedAndTimeoutCommands($this->lockTimeout);

        $results = [];
        foreach ($failedCommands as $command) {
            $results[$command->getId()] = [
                'lastReturnCode' => $command->getLastReturnCode(),
                'locked' => $command->getLocked() ? 'true' : 'false',
                'lastExecution' => $command->getLastExecution(),
            ];
        }

        $response = new JsonResponse();
        $response->setContent(json_encode($results));
        $response->setStatusCode(count($results) > 0 ? Response::HTTP_EXPECTATION_FAILED : Response::HTTP_OK);

        return $response;
    }
}
