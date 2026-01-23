<?php
declare(strict_types=1);

final class View {
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../Views/' . $layout . '.php';

        if (!is_file($viewFile)) throw new RuntimeException("View not found: $viewFile");
        if (!is_file($layoutFile)) throw new RuntimeException("Layout not found: $layoutFile");

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}