<?php

namespace Akyos\CanopeeSDK\Service;

use App\Class\Query;
use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProviderService
{
    private mixed $user;
    private string $clientId;
    private string $clientSecret;
    private string $canopeeUrl;
    private ?string $accessToken = null;
    private ?string $refresh_token = null;
    private GenericProvider $client;

    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private ContainerInterface $container
    ){
        $this->security = $security;
        $this->user = $this->security->getUser();
        $this->clientId = $this->container->getParameter('client_id');
        $this->clientSecret = $this->container->getParameter('client_secret');
        $this->canopeeUrl = $this->container->getParameter('endpoint');
        
        if ($this->user && method_exists($this->user, 'getAccessToken')){
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
    }

    public function new(string $resouces = null, ?string $method = 'GET'): Query
    {
        return new Query($this, $method, $resouces);
    }

    public function get(Query $query): \stdClass
    {
        $request = $this->client->getAuthenticatedRequest(
            $query->getMethod(),
            $this->canopeeUrl.$query->getResource().'?'.http_build_query($query->getQueryParams()),
            $this->accessToken
        );
        try {
            $response = $this->client->getResponse($request);
        } catch (\Exception $e) {
            $this->refreshToken();
            $request = $this->client->getAuthenticatedRequest(
                'GET',
                $this->canopeeUrl.$query->getResource(),
                $this->accessToken
            );
            $response = $this->client->getResponse($request);
        }
        return json_decode($response->getBody()->getContents());
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
