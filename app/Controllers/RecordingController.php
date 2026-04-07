<?php
declare(strict_types=1);

use App\Services\RecordingSearch;

final class RecordingController extends Controller {
    public function index(): void {
        $subjectRaw = $_GET['subject'] ?? [];
        if (is_array($subjectRaw)) {
            $subjects = get_array('subject');
        } else {
            $subjectInput = trim((string)$subjectRaw);
            $subjects = $subjectInput === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $subjectInput)), fn($x) => $x !== ''));
        }

        $params = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'place' => trim((string)($_GET['place'] ?? '')),
            'genre' => trim((string)($_GET['genre'] ?? '')),
            'subgenre'  => get_array('subgenre'),
            'subgenres' => get_array('subgenre'),
            'subject'   => $subjects,
            'subjects'  => $subjects,
            'has_en' => (int)($_GET['has_en'] ?? 0),
            'has_transcription' => (int)($_GET['has_transcription'] ?? 0),
            'transcription_q' => trim((string)($_GET['transcription_q'] ?? '')),
            'sort' => (string)($_GET['sort'] ?? 'date_desc'),
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
        ];

        $search = new RecordingSearch();
        $result = $search->search($params);

        $searchPanel = SearchPanel::recordings();
        $searchPanel['subgenres_by_genre'] = Taxonomy::subgenresByGenre();

        $this->render('recordings/index', [
            'result' => $result,
            'params' => $params,
            'genres' => Taxonomy::genres(),
            'subgenres_all' => Taxonomy::subgenres(),
            'subgenres_by_genre' => Taxonomy::subgenresByGenre(),
            'subjects_all'  => Taxonomy::subjects(),
            'places_all' => Taxonomy::places(1500),
        ]);
    }

    public function show(string $id): void {
        $rec = Recording::find($id);
        if (!$rec) {
            http_response_code(404);
            echo "Recording not found";
            return;
        }
        $relatedRecordings = Recording::related($rec);

        $relatedRecords = Recording::related(
            (string)($rec['recording_id'] ?? ''),
            (string)($rec['informant_id'] ?? ''),
            (string)($rec['genre_id'] ?? ''),
            3
        );

        $this->render('recordings/show', [
            'rec' => $rec,
            'relatedRecords' => $relatedRecords,
        ]);
    }

    public function downloadTranscription(string $id): void {
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $id)) {
            http_response_code(400);
            echo 'Invalid recording ID';
            return;
        }

        $rec = Recording::find($id);
        if (!$rec) {
            http_response_code(404);
            echo 'Recording not found';
            return;
        }

        $transcriptionText = trim((string)($rec['transcription_text'] ?? ''));
        if ($transcriptionText === '') {
            http_response_code(404);
            echo 'No transcription available';
            return;
        }

        $filename = $rec["title"] . '.txt';

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . strlen($transcriptionText));

        echo $transcriptionText;
        exit;
    }
}