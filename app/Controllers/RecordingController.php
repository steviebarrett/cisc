<?php
declare(strict_types=1);

use App\Services\RecordingSearch;

final class RecordingController extends Controller {
    public function index(): void {
        $params = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'place' => trim((string)($_GET['place'] ?? '')),
            'genre' => trim((string)($_GET['genre'] ?? '')),
            'subgenres' => get_array('subgenre'),
            'subjects'  => get_array('subject'),
            'has_en' => (int)($_GET['has_en'] ?? 0),
            'sort' => (string)($_GET['sort'] ?? 'date_asc'),
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
        ];

        $search = new RecordingSearch();
        $result = $search->search($params);

        $this->render('recordings/index', [
            'result' => $result,
            'params' => $params,
            'genres' => Taxonomy::genres(),
            'subgenres_all' => Taxonomy::subgenres(),
            'subjects_all'  => Taxonomy::subjects(),
        ]);
    }

    public function show(string $id): void {
        $rec = Recording::find($id);
        if (!$rec) {
            http_response_code(404);
            echo "Recording not found";
            return;
        }

        $this->render('recordings/show', ['rec' => $rec]);
    }
}