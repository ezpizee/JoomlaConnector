<?php

namespace EzpizeeJoomla\ContextProcessors\ExpireIn;

use Ezpizee\MicroservicesClient\Client;
use EzpizeeJoomla\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  protected function requiredAccessToken(): bool {return false;}

  protected function allowedMethods(): array {return ['GET'];}

  protected function validRequiredParams(): bool {return true;}

  public function processContext(): void {
      $tokenKey = $this->microserviceClient->getConfig(Client::KEY_ACCESS_TOKEN);
      $token = $this->microserviceClient->getToken($tokenKey);
      $this->setContextData(['expire_in' => $token->getExpireIn()]);
  }
}
