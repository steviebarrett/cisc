<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
        ]);
    }

    public function show_map(): void
    {
        $this->render('home/map', [
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
        ]);
    }
}
