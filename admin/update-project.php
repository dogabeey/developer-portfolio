<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$role = trim($_POST['role'] ?? '');
$description = trim($_POST['description'] ?? '');
$url = trim($_POST['project_url'] ?? '');
$thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');
$screenshots = array_values(array_filter(array_map('trim', preg_split('/\R/', $_POST['screenshot_urls'] ?? ''))));
$useMetadataDescription = isset($_POST['use_metadata_description']);
$useMetadataScreenshots = isset($_POST['use_metadata_screenshots']);

if (!$id || !$title || !$role || ($url && !is_http_url($url)) || ($thumbnailUrl && !is_http_url($thumbnailUrl)) || array_filter($screenshots, fn ($screenshot) => !is_http_url($screenshot))) {
    flash('Please enter a title, role, and valid http(s) links if provided.');
    header('Location: edit-project.php?id=' . $id); exit;
}

$metadataTitle = $useMetadataDescription && $url ? storefront_title($url) : null;
$metadataDescription = $useMetadataDescription && $url ? storefront_description($url) : null;
$metadataThumbnailUrl = $useMetadataDescription && $url ? fetch_project_thumbnail($url) : null;
$metadataScreenshots = $useMetadataScreenshots && $url ? fetch_project_screenshots($url) : [];

$database = database();
$database->beginTransaction();
try {
    $statement = $database->prepare('UPDATE projects SET title = ?, role = ?, description = ?, metadata_title = ?, metadata_description = ?, metadata_thumbnail_url = ?, use_metadata_description = ?, use_metadata_screenshots = ?, project_url = ?, thumbnail_url = ?, sort_order = ?, is_published = ? WHERE id = ?');
    $statement->execute([$title, $role, $description, $metadataTitle, $metadataDescription, $metadataThumbnailUrl, $useMetadataDescription ? 1 : 0, $useMetadataScreenshots ? 1 : 0, $url ?: null, $thumbnailUrl ?: null, (int) ($_POST['sort_order'] ?? 0), isset($_POST['is_published']) ? 1 : 0, $id]);
    $database->prepare('DELETE FROM project_screenshots WHERE project_id = ?')->execute([$id]);
    $screenshotStatement = $database->prepare('INSERT INTO project_screenshots (project_id, image_url, source, sort_order) VALUES (?, ?, ?, ?)');
    foreach ($screenshots as $index => $screenshot) {
        $screenshotStatement->execute([$id, $screenshot, 'manual', $index]);
    }
    foreach ($metadataScreenshots as $index => $screenshot) {
        $screenshotStatement->execute([$id, $screenshot, 'metadata', $index]);
    }
    $database->commit();
} catch (Throwable $error) {
    $database->rollBack();
    throw $error;
}

flash('Project updated successfully.');
header('Location: index.php');
