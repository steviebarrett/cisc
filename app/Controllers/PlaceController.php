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
                'place' => (string)($row['name'] ?? ''),
               // 'place_scotland' => (string)($row['place_scotland'] ?? ''),
                'inf_count' => (int)($row['inf_count'] ?? 0),
                'lat' => isset($row['latitude']) ? (float)$row['latitude'] : null,
                'lng' => isset($row['longitude']) ? (float)$row['longitude'] : null,
                //'sc_lat' => isset($row['sc_lat']) ? (float)$row['sc_lat'] : null,
                //'sc_lng' => isset($row['sc_lng']) ? (float)$row['sc_lng'] : null,
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