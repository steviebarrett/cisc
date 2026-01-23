<?php
declare(strict_types=1);

abstract class Controller {
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void {
        View::render($view, $data, $layout);
    }
}