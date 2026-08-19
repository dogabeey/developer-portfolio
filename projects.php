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
        $project['screenshots'] = $manualScreenshots;
        if ($project['use_metadata_description'] && $project['project_url']) {
            $metadataTitle = storefront_title($project['project_url']);
            $metadataDescription = storefront_description($project['project_url']);
            $metadataThumbnailUrl = fetch_project_thumbnail($project['project_url']);
            if ($metadataTitle) {
                $project['title'] = $metadataTitle;
                $update = database()->prepare('UPDATE projects SET metadata_title = ? WHERE id = ?');
                $update->execute([$metadataTitle, $project['id']]);
            }
            if ($metadataDescription) {
                $project['description'] = $metadataDescription;
                $update = database()->prepare('UPDATE projects SET metadata_description = ? WHERE id = ?');
                $update->execute([$metadataDescription, $project['id']]);
            }
            if ($metadataThumbnailUrl) {
                $project['thumbnail_url'] = $metadataThumbnailUrl;
                $update = database()->prepare('UPDATE projects SET metadata_thumbnail_url = ? WHERE id = ?');
                $update->execute([$metadataThumbnailUrl, $project['id']]);
            }
        }
        if ($project['use_metadata_screenshots'] && $project['project_url']) {
            $metadataScreenshots = fetch_project_screenshots($project['project_url']);
            if ($metadataScreenshots) {
                $project['screenshots'] = $metadataScreenshots;
                $database = database();
                $database->beginTransaction();
                try {
                    $database->prepare("DELETE FROM project_screenshots WHERE project_id = ? AND source = 'metadata'")->execute([$project['id']]);
                    $insert = $database->prepare("INSERT INTO project_screenshots (project_id, image_url, source, sort_order) VALUES (?, ?, 'metadata', ?)");
                    foreach ($metadataScreenshots as $index => $imageUrl) {
                        $insert->execute([$project['id'], $imageUrl, $index]);
                    }
                    $database->commit();
                } catch (Throwable $error) {
                    $database->rollBack();
                    throw $error;
                }
            }
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
