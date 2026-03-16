<?php
declare(strict_types=1);

final class HomeController extends Controller
{

    public function index(): void
    {
        $searchPanel = SearchPanel::recordings();
        $this->render('home/index', [
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
          'searchPanel' => $searchPanel,
        ]);
    }

    public function show_map(): void
    {
        $searchPanel = SearchPanel::recordings();
        $this->render('home/map', [
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
          'searchPanel' => $searchPanel,
        ]);
    }

    public function about(): void
    {
        $searchPanel = SearchPanel::recordings();
        $this->render('home/about', [
            'enableSearchPanel' => true,
            'searchPanelType' => 'recordings',
            'headerSearchOpen' => false,
            'searchPanel' => $searchPanel,
        ]);
    }

    public function how_to_use(): void
    {
        $searchPanel = SearchPanel::recordings();
        $this->render('home/how_to_use', [
            'enableSearchPanel' => true,
            'searchPanelType' => 'recordings',
            'headerSearchOpen' => false,
            'searchPanel' => $searchPanel,
        ]);
    }

    public function thanks(): void
    {
        $searchPanel = SearchPanel::recordings();
        $this->render('home/thanks', [
            'enableSearchPanel' => true,
            'searchPanelType' => 'recordings',
            'headerSearchOpen' => false,
            'searchPanel' => $searchPanel,
        ]);
    }


}
