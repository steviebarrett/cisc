<?php
declare(strict_types=1);

use App\Services\RecordingSearch;

final class HomeController extends Controller
{

    public function index(): void
    {
        // Fetch random informants
        define('NUM_INFORMANTS', 5);
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
            SELECT 
                i.first_name,
                i.last_name,
                i.informant_id,
                ii.filename,
                p.name AS place_name,
                COUNT(r.recording_id) AS num_recs
            FROM informant i
            LEFT JOIN informant_image ii 
                ON ii.informant_id = i.informant_id 
               AND ii.slot = 1
            LEFT JOIN place p 
                ON p.id = i.place_canada_id
            LEFT JOIN recording r 
                ON r.informant_id = i.informant_id
            GROUP BY 
                i.first_name,
                i.last_name,
                i.informant_id,
                ii.filename,
                p.name
            ORDER BY RAND()
            LIMIT  " . NUM_INFORMANTS);

        $stmt->execute();
        $featuredInformants = $stmt->fetchAll();

        // Fetch random recordings
        define('NUM_RECORDINGS', 5);
        $search = new RecordingSearch();
        $recordings = $search->search([]);
        $recordings = $recordings['rows'] ?? [];

        if (!empty($recordings)) {
            $featuredRecordings = $recordings;
            shuffle($featuredRecordings);
            $featuredRecordings = array_slice($featuredRecordings, 0, min(5, count($featuredRecordings)));
        } else {
            $featuredRecordings = [];
        }

        $searchPanel = SearchPanel::recordings();
        $this->render('home/index', [
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
          'searchPanel' => $searchPanel,
            'featuredInformants' => $featuredInformants,
            'featuredRecordings' => $featuredRecordings,
        ]);
    }

    public function show_map(): void
    {
        $searchPanel = SearchPanel::recordings();
        $this->render('home/map', [
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
          'fullWidth' => true,
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
