<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

try {
    require __DIR__ . '/config.php';
    $projectId = (int) ($_POST['project_id'] ?? 0);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!$projectId || !$ipAddress) {
        http_response_code(400);
        exit;
    }

    $project = database()->prepare('SELECT id FROM projects WHERE id = ? AND is_published = 1');
    $project->execute([$projectId]);
    if (!$project->fetchColumn()) {
        http_response_code(404);
        exit;
    }

    $ipHash = hash_hmac('sha256', $ipAddress, CLICK_HASH_SALT);
    $record = database()->prepare('INSERT IGNORE INTO project_clicks (project_id, ip_hash) VALUES (?, ?)');
    $record->execute([$projectId, $ipHash]);
    echo json_encode(['recorded' => true]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['recorded' => false]);
}
