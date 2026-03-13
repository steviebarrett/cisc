<?php
declare(strict_types=1);

final class InformantController extends Controller {

    public function index(): void {
        $params = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
            'sort' => (string)($_GET['sort'] ?? 'name_asc'),
        ];

        $result = Informant::search($params);

        $this->render('informants/index', [
            'params' => $params,
            'kw' => $params['q'],
            'result' => $result,
          'enableSearchPanel' => true,
          'searchPanelType' => 'recordings',
          'headerSearchOpen' => false,
        ]);
    }

    public function show(string $id): void {
        $inf = Informant::find($id);
        if (!$inf) {
            http_response_code(404);
            echo "Informant not found";
            return;
        }

        // List recordings by this informant (simple)
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
      SELECT r.recording_id, r.title, r.recording_date, g.name AS genre_name
      FROM recording r
      LEFT JOIN genre g ON g.genre_id = r.genre_id
      WHERE r.informant_id = :id
      ORDER BY r.recording_date ASC, r.recording_id ASC
    ");
        $stmt->execute([':id' => $id]);
        $recs = $stmt->fetchAll();

        $this->render('informants/show', [
            'inf' => $inf,
            'recs' => $recs,
        ]);
    }
}
