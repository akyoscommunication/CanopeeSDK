<?php

namespace Akyos\CanopeeSDK\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Akyos\CanopeeModuleSDK\Class\Patch;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{
    #[Route('/notifications/assets/{id}', name: 'notifications')]
    public function notifications(
        Request $request,
        int $id,
        ProviderService $provider,
    ): Response
    {
        $post = (new Patch('notifications'))->setPathParams(['id' => $id])->setBody(['state' => 'read']);
        $provider->initialize('canopee')->send($post);

        return new Response("", Response::HTTP_OK);
    }
}
