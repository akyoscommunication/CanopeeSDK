<?php

namespace Akyos\CanopeeSDK\Service;

use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class ProviderService
{
    private mixed $user;
    private string $clientId = "216ce83e11f99298d324f6060598f670";
    private string $clientSecret = "61ef731cea95921a479754321cee2dcd517d1d12f85b313de6bdadfb8155d1ecd93b3e2afb0e2bdd57aed569291737ced3ae5c8228ba7e43c9bfc95cdb7b5f94";
    private string $canopeeUrl = "http://localhost:8000/";
    private ?string $accessToken = null;
    private ?string $refresh_token = null;
    private GenericProvider $client;

    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ){
        $this->security = $security;
        $this->user = $this->security->getUser();
        if(!method_exists($this->user, 'getAccessToken')) {
            throw new \Exception('User must have a UserCanopeeSDK Trait');
        }
        $this->refresh_token = $this->user->getRefreshToken();

        $this->client = new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'urlAuthorize' => $this->canopeeUrl.'authorize',
            'urlAccessToken' => $this->canopeeUrl.'token',
            'urlResourceOwnerDetails' => $this->canopeeUrl,
        ]);

        $this->accessToken = $this->user->getAccessToken();
        if($this->accessToken === null || $this->accessToken === "") {
            $response = $this->client->getAccessToken('password', [
                'username' => $this->user->getUserIdentifier(),
                'password' => $this->user->getModuleToken(),
            ]);
            $this->user->setRefreshToken($response->getRefreshToken());
            $this->accessToken = $response->getToken();
            $this->user->setAccessToken($this->accessToken);
        }
        $entityManager->flush();
    }

    public function get(string $resource): string
    {
        $request = $this->client->getAuthenticatedRequest(
            'GET',
            $this->canopeeUrl.'api/'.$resource,
            $this->accessToken
        );
        try {
            $response = $this->client->getResponse($request);
        } catch (\Exception $e) {
            $this->refreshToken();
            $request = $this->client->getAuthenticatedRequest(
                'GET',
                $this->canopeeUrl.'api/'.$resource,
                $this->accessToken
            );
            $response = $this->client->getResponse($request);
        }
        return $response->getBody()->getContents();
    }

    private function refreshToken(): void
    {
        $response = $this->client->getAccessToken('refresh_token', [
            'refresh_token' => $this->refresh_token,
        ]);
        $this->user->setRefreshToken($response->getRefreshToken());
        $this->accessToken = $response->getToken();
        $this->user->setAccessToken($this->accessToken);
        $this->entityManager->flush();
    }
}
