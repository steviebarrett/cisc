<?php
declare(strict_types=1);

final class ComposerController extends Controller {
    public function show(string $id): void {
        $c = Composer::find($id);
        if (!$c) {
            http_response_code(404);
            echo "Composer not found";
            return;
        }

        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
      SELECT r.recording_id, r.title, r.recording_date, i.first_name AS informant_first, i.last_name AS informant_last
      FROM recording r
      JOIN informant i ON i.informant_id = r.informant_id
      WHERE r.composer_id = :id
      ORDER BY r.recording_date ASC, r.recording_id ASC
    ");
        $stmt->execute([':id' => $id]);
        $recs = $stmt->fetchAll();

        $this->render('composers/show', [
            'composer' => $c,
            'recs' => $recs,
        ]);
    }
}