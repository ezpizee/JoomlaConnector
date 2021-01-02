<?php

namespace EzpizeeJoomla;

use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Joomla\CMS\Factory;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function keepToken(Token $token): void {
        if ($this->key) {
            Factory::getSession()->set($this->key, serialize($token));
        }
    }

    public function getToken(): Token {
        if ($this->key && Factory::getSession()->has($this->key)) {
            $token = unserialize(Factory::getSession()->get($this->key));
            if ($token instanceof Token) {
                return $token;
            }
        }
        return new Token([]);
    }
}
