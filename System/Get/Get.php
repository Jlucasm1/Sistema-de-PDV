<?php

namespace System\Get;

use System\Session\Session;

class Get
{
    public function redirectTo(string $route): void
    {
        $url = BASEURL . '/' . ltrim($route, '/');
        header('Location: ' . Session::appendTabToUrl($url));
        exit;
    }

    public function param(string $key): ?string
    {
        return $_GET[$key] ?? null;
    }
}
