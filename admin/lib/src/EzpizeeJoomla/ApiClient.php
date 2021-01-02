<?php

namespace EzpizeeJoomla;

use Ezpizee\MicroservicesClient\Client;
use Ezpizee\Utils\Request;
use Ezpizee\Utils\RequestEndpointValidator;
use EzpizeeJoomla\ContextProcessors\BaseContextProcessor;

class ApiClient
{
    /**
     * @var Client
     */
    private $microserviceClient;
    private $endpoints = [
        '/api/v1/joomla/refresh/token' => 'EzpizeeJoomla\ContextProcessors\RefreshToken',
        '/api/v1/joomla/expire-in' => 'EzpizeeJoomla\ContextProcessors\ExpireIn',
        '/api/v1/joomla/authenticated-user' => 'EzpizeeJoomla\ContextProcessors\AuthenticatedUser',
        '/api/v1/joomla/crsf-token' => 'EzpizeeJoomla\ContextProcessors\CRSFToken',
        '/api/v1/joomla/user/profile' => 'EzpizeeJoomla\ContextProcessors\User\Profile'
    ];

    public function __construct(Client $client)
    {
        $this->microserviceClient = $client;
    }

    public function load(string $uri): array
    {
        $uri = str_replace('//', '/', '/'.$uri);
        RequestEndpointValidator::validate($uri, $this->endpoints);
        $namespace = RequestEndpointValidator::getContextProcessorNamespace();
        $class = new $namespace();
        if ($class instanceof BaseContextProcessor) {
            $class->setMicroServiceClient($this->microserviceClient);
            $request = new Request();
            $class->setRequest($request);
            return $class->getContext();
        }
        return ['code'=>404, 'message'=>'Invalid namespace: '.$namespace, 'data'=>null];
    }
}
