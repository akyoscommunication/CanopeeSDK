<?php

namespace Akyos\CanopeeSDK\Service;

use Akyos\CanopeeSDK\Class\Query;
use League\Bundle\OAuth2ServerBundle\Entity\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\RequestInterface;
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
        $this->clientId = $this->container->getParameter('api')['client_id'];
        $this->clientSecret = $this->container->getParameter('api')['client_secret'];
        $this->canopeeUrl = $this->container->getParameter('api')['endpoint'];

        $this->client = new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'urlAuthorize' => $this->canopeeUrl.'authorize',
            'urlAccessToken' => $this->canopeeUrl.'token',
            'urlResourceOwnerDetails' => $this->canopeeUrl,
        ]);
    }

    public function new(string $resouces = null, string $method = 'GET', $user = null): Query
    {
        $this->user = $this->security->getUser();
        if($user !== null) {
            $this->user = $user;
        }
        if ($this->user && method_exists($this->user, 'getAccessToken') && $_SERVER['APP_ENV'] !== 'test'){
            $this->refresh_token = $this->user->getRefreshToken();
            $this->accessToken = $this->user->getAccessToken();
            if($this->accessToken === null || $this->accessToken === "") {
                $response = $this->client->getAccessToken('password', [
                    'username' => str_contains($this->user->getUserIdentifier(), '@') ? $this->user->getId() : $this->user->getUserIdentifier(),
                    'password' => $this->user->getModuleToken(),
                ]);
                $this->user->setRefreshToken($response->getRefreshToken());
                $this->accessToken = $response->getToken();
                $this->user->setAccessToken($this->accessToken);
            }
            $this->entityManager->flush();
        }
        return new Query($this, $method, $resouces);
    }

    public function get(Query $query): \stdClass
    {
        if($_SERVER['APP_ENV'] !== 'test') {
            $request = $this->request($query);
            try {
                $response = $this->client->getResponse($request);
            } catch (\Exception $e) {
                $this->refreshToken();
                $request = $this->request($query);
                $response = $this->client->getResponse($request);
            }
            return json_decode($response->getBody()->getContents());
        }
        return (object) [
            'hydra:member' => [],
            'hydra:totalItems' => 0,
            '@type' => '',
            '@id' => '',
            '@context' => '',
        ];
    }

    private function request(Query $query): RequestInterface
    {
        $pathParams = '';
        if(!empty($query->getBody())){
            $options['body'] = json_encode($query->getBody());
        }
        foreach ($query->getPathParams() as $value) {
            $pathParams .= '/'.$value;
        }
        return $this->client->getAuthenticatedRequest(
            $query->getMethod(),
            $this->canopeeUrl . 'api/' . $query->getResource(). $pathParams . '?' . http_build_query($query->getQueryParams()),
            $this->accessToken,
            $options ?? []
        );
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
