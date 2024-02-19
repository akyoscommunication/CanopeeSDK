<?php

namespace Akyos\CanopeeSDK\Controller\Faq;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    #[Route('/faq', name: 'faq', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        return $this->render('@CanopeeSDK/faq/index.html.twig');
    }
}
