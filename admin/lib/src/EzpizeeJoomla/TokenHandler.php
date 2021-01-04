<?php

namespace EzpizeeJoomla;

use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Exception;
use Ezpizee\Utils\Logger;
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

    public function setCookie(string $name, string $value, int $expire=0, string $path=''): void
    {
        try {
            Factory::getApplication()->input->cookie->set(
                $name, $value, $expire, $path
            );
        }
        catch (Exception $e) {
            Logger::error($e->getMessage());
        }
    }
}
