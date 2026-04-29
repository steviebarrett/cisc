<?php
declare(strict_types=1);

use App\Services\RecordingSearch;

final class HomeController extends Controller
{

    public function index(): void
    {
        $this->render('home/index', [
            'fullWidth' => true,
            'featuredInformants' => Informant::randomFeatured(5),
            'featuredRecordings' => Recording::randomFeatured(3),
        ]);
    }

    public function holding(): void
    {
        $this->render('home/holding', [], 'layouts/holding');
    }

    public function show_map(): void
    {
        $this->render('home/map', [
            'fullWidth' => true,
        ]);
    }

    public function about(): void
    {
        $this->render('home/about', [
        ]);
    }

    public function how_to_use(): void
    {
        $this->render('home/how_to_use', [
        ]);
    }

    public function thanks(): void
    {
        $this->render('home/thanks', [
        ]);
    }


}