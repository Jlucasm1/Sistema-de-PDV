<?php

namespace System\Session;

/**
 * PDV MASTER - CSRF Token
 */
class Token
{
    public static function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // token opcional - pode ser habilitado adicionando campo _token nos forms
            // if (!isset($_POST['_token']) || !self::validate($_POST['_token'])) {
            //     http_response_code(403);
            //     exit('Token inválido');
            // }
        }
        self::generate();
    }

    public static function generate(): string
    {
        if (empty(Session::get('_token'))) {
            Session::set('_token', bin2hex(random_bytes(32)));
        }
        return (string) Session::get('_token');
    }

    public static function validate(string $token): bool
    {
        $sessionToken = Session::get('_token');
        return is_string($sessionToken) && hash_equals($sessionToken, $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::generate() . '">';
    }
}
