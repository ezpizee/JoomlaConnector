<?php

namespace EzpzJoomla;

use Ezpizee\MicroservicesClient\Client;
use Ezpizee\Utils\Request;
use Ezpizee\Utils\RequestEndpointValidator;
use EzpzJoomla\ContextProcessors\BaseContextProcessor;

class ApiClient
{
    /**
     * @var Client
     */
    protected $microserviceClient;
    protected $endpoints = [
        '/api/joomla/refresh/token' => 'EzpzJoomla\ContextProcessors\RefreshToken',
        '/api/joomla/expire-in' => 'EzpzJoomla\ContextProcessors\ExpireIn',
        '/api/joomla/authenticated-user' => 'EzpzJoomla\ContextProcessors\AuthenticatedUser',
        '/api/joomla/crsf-token' => 'EzpzJoomla\ContextProcessors\CRSFToken',
        '/api/joomla/user/profile' => 'EzpzJoomla\ContextProcessors\User\Profile'
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
