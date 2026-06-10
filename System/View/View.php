<?php

namespace System\View;

/**
 * PDV MASTER - Motor de Views
 */
class View
{
    private string  $viewName   = '';
    private ?string $withLayout = null;
    private array   $data       = [];

    public function view(string $viewName, ?string $withLayout = null, array|bool $data = false): void
    {
        $this->viewName   = $viewName;
        $this->withLayout = $withLayout;
        $this->data       = is_array($data) ? $data : [];

        if ($this->data) {
            extract($this->data);
        }

        $viewFile = $this->resolveViewPath($viewName);

        if ($withLayout) {
            $layoutFile = $this->resolveLayoutPath($withLayout);
            require_once $layoutFile;
        } else {
            require_once $viewFile;
        }
    }

    public function viewRender(): void
    {
        if ($this->data) {
            extract($this->data);
        }
        $viewFile = $this->resolveViewPath($this->viewName);
        require_once $viewFile;
    }

    private function resolveViewPath(string $name): string
    {
        $path = BASE_PATH . "/App/Views/{$name}.php";
        if (!file_exists($path)) {
            $this->abort404("View não encontrada: {$name}");
        }
        return $path;
    }

    private function resolveLayoutPath(string $layout): string
    {
        $path = BASE_PATH . "/App/Views/Layouts/{$layout}.php";
        if (!file_exists($path)) {
            $this->abort404("Layout não encontrado: {$layout}");
        }
        return $path;
    }

    private function abort404(string $msg): never
    {
        http_response_code(404);
        echo "<p style='font-family:sans-serif;color:red;'>404 - {$msg}</p>";
        exit;
    }
}
