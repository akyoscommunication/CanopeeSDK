<?php

namespace Akyos\CanopeeSDK\Service;

use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class ProviderService
{
    private static $provider = null;
    private Security $security;
    private UserInterface $user;
    private string $clientId = "216ce83e11f99298d324f6060598f670";
    private string $clientSecret = "61ef731cea95921a479754321cee2dcd517d1d12f85b313de6bdadfb8155d1ecd93b3e2afb0e2bdd57aed569291737ced3ae5c8228ba7e43c9bfc95cdb7b5f94";
    private string $canopeeUrl = "http://localhost:8000/";
    private ?string $accessToken = null;
    private ?string $refresh_token = null;
    private GenericProvider $client;

    protected function __construct(Security $security
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

        if($this->accessToken === null) {
            $this->accessToken = $this->client->getAccessToken('password', [
                'username' => $this->user->getUsername(),
                'password' => $this->user->getPassword(),
            ]);
            $this->user->setRefreshToken($this->accessToken->getRefreshToken());
            $this->user->setAccessToken($this->accessToken);
        }
    }

    public static function getInstance($security): ProviderService
    {
        if(self::$provider === null) {
            self::$provider = new ProviderService($security);
        }
        return self::$provider;
    }

    public function get(string $resource): string
    {
        $request = $provider->getAuthenticatedRequest(
            'GET',
            $this->canopeeUrl.'api/'.$resource,
            $this->accessToken
        );
        if($response->getStatusCode() === 401) {
            $this->refreshToken();
            $request = $provider->getAuthenticatedRequest(
                'GET',
                $this->canopeeUrl.'api/'.$resource,
                $this->accessToken
            );
        }
        $response = $provider->getResponse($request);
        return $response->getBody()->getContents();
    }

    private function refreshToken(): void
    {
        $this->accessToken = $this->client->getAccessToken('refresh_token', [
            'refresh_token' => $this->refresh_token,
        ]);
        $this->user->setRefreshToken($this->accessToken->getRefreshToken());
        $this->user->setAccessToken($this->accessToken);
    }
}
