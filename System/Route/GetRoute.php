<?php

namespace System\Route;

/**
 * PDV MASTER - Extrai controller e método da URL
 */
class GetRoute
{
    private string $uri;

    public function __construct()
    {
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->uri  = trim(substr($requestUri, strlen($scriptName)), '/');
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getControllerName(): string
    {
        $parts = explode('/', $this->uri);
        return ucfirst($parts[0] ?? 'Login') . 'Controller';
    }

    public function getMethodName(): string
    {
        $parts = explode('/', $this->uri);
        return $parts[1] ?? 'index';
    }

    public function getControllerNameAliases(): string
    {
        $parts = explode('/', $this->uri);
        return $parts[0] ?? '';
    }

    public function getMethodNameAliases(): string
    {
        $parts = explode('/', $this->uri);
        return $parts[1] ?? '';
    }
}
