<?php

namespace Akyos\CanopeeSDK\EventListener;

use Akyos\CanopeeSDK\Service\UserAccessRightsService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class UserAccessRightSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UserAccessRightsService $userAccessRightsService,
    ){}

    /**
     * @throws \Exception
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $user = $this->security->getUser();
        $userAccessRights = $this->userAccessRightsService->getUserAccessRights($user);

        if (!count($userAccessRights)) {
            throw new \Exception('You do not have access to this module');
        }

        $session = $event->getRequest()->getSession();
        if(count($userAccessRights) === 1) {
            $session->set('userAccessRights', $userAccessRights[0]);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
