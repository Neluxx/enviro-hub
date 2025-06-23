<?php

declare(strict_types=1);

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
