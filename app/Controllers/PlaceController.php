<?php
declare(strict_types=1);

use app\Services\PlaceSearch;

final class PlaceController extends Controller
{
    public function index(): void
    {
        $params = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
            'sort' => (string)($_GET['sort'] ?? 'name_asc'),
        ];

        $search = new PlaceSearch();
        $result = $search->search($params);

        $mapData = array_map(static function (array $row): array {
            return [
                'place' => (string)($row['place'] ?? ''),
                'place_scotland' => (string)($row['place_scotland'] ?? ''),
                'rec_count' => (int)($row['rec_count'] ?? 0),
                'cn_lat' => isset($row['cn_lat']) ? (float)$row['cn_lat'] : null,
                'cn_lng' => isset($row['cn_lng']) ? (float)$row['cn_lng'] : null,
                'sc_lat' => isset($row['sc_lat']) ? (float)$row['sc_lat'] : null,
                'sc_lng' => isset($row['sc_lng']) ? (float)$row['sc_lng'] : null,
            ];
        }, $result['rows']);

        $this->render('places/index', [
            'params' => $params,
            'kw' => $params['q'],
            'mapData' => $mapData,
            'result' => $result,
        ]);
    }
}