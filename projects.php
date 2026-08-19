<?php
declare(strict_types=1);

session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'cookie_path' => '/']);
header('Content-Type: application/json; charset=utf-8');

try {
    require __DIR__ . '/config.php';
    require_once __DIR__ . '/thumbnail.php';
    $statement = database()->query(
        'SELECT id, title, role, description, metadata_title, metadata_description, metadata_thumbnail_url, use_metadata_description, use_metadata_screenshots, project_url, thumbnail_url
         FROM projects
         WHERE is_published = 1
         ORDER BY sort_order ASC, created_at DESC'
    );
    $projects = $statement->fetchAll(PDO::FETCH_ASSOC);
    $screenshots = database()->query(
        'SELECT s.project_id, s.image_url, s.source
         FROM project_screenshots s
         INNER JOIN projects p ON p.id = s.project_id
         WHERE p.is_published = 1
         ORDER BY s.project_id ASC, s.sort_order ASC, s.id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);
    $screenshotsByProject = [];
    foreach ($screenshots as $screenshot) {
        $screenshotsByProject[$screenshot['project_id']][$screenshot['source']][] = $screenshot['image_url'];
    }
    foreach ($projects as &$project) {
        $manualScreenshots = $screenshotsByProject[$project['id']]['manual'] ?? [];
        $metadataScreenshots = $screenshotsByProject[$project['id']]['metadata'] ?? [];
        $project['screenshots'] = $manualScreenshots;
        if ($project['use_metadata_description']) {
            $project['title'] = $project['metadata_title'] ?: $project['title'];
            $project['description'] = $project['metadata_description'] ?: $project['description'];
            $project['thumbnail_url'] = $project['metadata_thumbnail_url'] ?: $project['thumbnail_url'];
        }
        if ($project['use_metadata_screenshots'] && $metadataScreenshots) {
            $project['screenshots'] = $metadataScreenshots;
        }
        unset($project['metadata_title'], $project['metadata_description'], $project['metadata_thumbnail_url'], $project['use_metadata_description'], $project['use_metadata_screenshots']);
        if (!isset($_SESSION['user_id'])) {
            unset($project['id']);
        }
    }
    unset($project);
    echo json_encode($projects, JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load projects.']);
}
