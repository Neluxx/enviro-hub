<?php

namespace App\Security;

class TokenAuthenticator
{
    private string $validToken;

    public function __construct(string $validToken)
    {
        $this->validToken = $validToken;
    }

    public function authenticate(string $bearerToken): bool
    {
        return $bearerToken === $this->validToken;
    }
}