<?php

namespace System\Controller;

use System\View\View;

/**
 * PDV MASTER - Controller Base
 */
class Controller
{
    private View $viewObject;

    public function __construct()
    {
        $this->viewObject = new View();
    }

    public function view(string $viewName, ?string $withLayout = null, array|bool $data = false): void
    {
        $this->viewObject->view($viewName, $withLayout, $data);
    }
}
