<?php

namespace Akyos\CanopeeSDK\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileController extends AbstractController
{
    #[Route('/stream_document', name: 'stream_document')]
    public function streamDocument(Request $request, KernelInterface $kernelprivate, HttpClientInterface $client, ParameterBagInterface $parameterBag): Response
    {
        $entity = $request->query->get('entity');
        $id = $request->query->get('id');
        $fieldName = $request->query->get('fieldName');
        $entityName = explode("\\", $entity);

        return (new FilesystemAdapter())->get($id.'_'.$fieldName.'_'.end($entityName), function (ItemInterface $item) use ($client, $parameterBag, $request, $entity, $id, $fieldName) {
            $item->expiresAfter(3600);

            $user = $this->getUser();
            $token = $user->getModuleToken();

            $response = $client->request(
                'GET',
                $parameterBag->get('canopee_endpoint').'module_stream_document?entity='.$entity.'&id='.$id.'&fieldName='.$fieldName.'&token='.$token
            );
            return new Response($response->getContent());
        });
    }

}
