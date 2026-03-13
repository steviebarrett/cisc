<?php

class SearchPanel
{
    public static function recordings(array $overrides = []): array
    {
        $subjects = get_array('subject');

        $params = array_merge([
            'q' => trim((string)($_GET['q'] ?? '')),
            'place' => trim((string)($_GET['place'] ?? '')),
            'genre' => trim((string)($_GET['genre'] ?? '')),
            'subgenre' => get_array('subgenre'),
            'subgenres' => get_array('subgenre'),
            'subject' => $subjects,
            'subjects' => $subjects,
            'has_en' => (int)($_GET['has_en'] ?? 0),
            'has_transcription' => (int)($_GET['has_transcription'] ?? 0),
            'transcription_q' => trim((string)($_GET['transcription_q'] ?? '')),
            'sort' => (string)($_GET['sort'] ?? 'date_desc'),
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
        ], $overrides);

        return [
            'params' => $params,
            'genres' => Taxonomy::genres(),
            'subgenres_all' => Taxonomy::subgenres(),
            'subjects_all' => Taxonomy::subjects(),
            'places_all' => Taxonomy::places(1500),
        ];
    }
}