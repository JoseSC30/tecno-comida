<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Str;

class UsuarioProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        return parent::retrieveByCredentials($this->mapCredentials($credentials));
    }

    private function mapCredentials(array $credentials): array
    {
        if (array_key_exists('email', $credentials)) {
            $credentials['usu_email'] = Str::lower($credentials['email']);
            unset($credentials['email']);
        }

        return $credentials;
    }
}
