<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$allProjects = database()->query('SELECT id, title, role, is_published FROM projects ORDER BY sort_order ASC, created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$id = (int) ($_GET['id'] ?? 0);
if (!$id && $allProjects) {
    $id = (int) $allProjects[0]['id'];
}
$statement = database()->prepare('SELECT * FROM projects WHERE id = ?');
$statement->execute([$id]);
$project = $statement->fetch(PDO::FETCH_ASSOC);
if (!$project) {
    http_response_code(404);
    exit('Project not found.');
}
$screenshotsStatement = database()->prepare("SELECT image_url FROM project_screenshots WHERE project_id = ? AND source = 'manual' ORDER BY sort_order ASC, id ASC");
$screenshotsStatement->execute([$id]);
$screenshotUrls = implode("\n", $screenshotsStatement->fetchAll(PDO::FETCH_COLUMN));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit <?= e($project['title']) ?> | Game Dev Portfolio</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="admin-header">
    <div><p class="eyebrow">Game Dev Portfolio</p><h1>Edit project</h1></div>
    <nav><a href="index.php">← Back to projects</a><a href="logout.php">Log out</a></nav>
  </header>
  <main class="admin-main">
    <section class="panel">
      <form class="project-form" method="post" action="update-project.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
        <label>Manual title <small>(used as fallback when store metadata is unavailable)</small><input name="title" maxlength="160" value="<?= e($project['title']) ?>" required></label>
        <label>Role<input name="role" maxlength="160" value="<?= e($project['role']) ?>" required></label>
        <label class="wide">Manual description <small>(optional fallback when store metadata is unavailable)</small><textarea name="description" rows="4"><?= e($project['description']) ?></textarea><span class="metadata-toggle"><input type="checkbox" name="use_metadata_description" value="1" <?= $project['use_metadata_description'] ? 'checked' : '' ?>> Use store metadata for title, description, and thumbnail</span></label>
        <label>Project link <small>(optional)</small><input type="url" name="project_url" maxlength="2048" value="<?= e($project['project_url']) ?>"></label>
        <label>Manual thumbnail URL <small>(optional fallback)</small><input type="url" name="thumbnail_url" maxlength="2048" value="<?= e($project['thumbnail_url']) ?>"></label>
        <label class="wide">Manual screenshot URLs <small>(one URL per line; used as fallback)</small><textarea name="screenshot_urls" rows="5"><?= e($screenshotUrls) ?></textarea><span class="metadata-toggle"><input type="checkbox" name="use_metadata_screenshots" value="1" <?= $project['use_metadata_screenshots'] ? 'checked' : '' ?>> Use store metadata for screenshots</span></label>
        <label>Display order<input type="number" name="sort_order" value="<?= (int) $project['sort_order'] ?>"></label>
        <label class="check wide"><input type="checkbox" name="is_published" value="1" <?= $project['is_published'] ? 'checked' : '' ?>> Publish on portfolio</label>
        <div class="wide"><button type="submit">Save changes</button></div>
      </form>
    </section>
    <section class="panel project-picker" aria-labelledby="switch-project">
      <h2 id="switch-project">Edit another project</h2>
      <?php if (count($allProjects) === 1): ?>
        <p class="muted">There are no other projects yet.</p>
      <?php else: ?>
        <div class="project-picker-list">
          <?php foreach ($allProjects as $listedProject): ?>
            <a class="<?= (int) $listedProject['id'] === (int) $project['id'] ? 'current' : '' ?>" href="edit-project.php?id=<?= (int) $listedProject['id'] ?>">
              <strong><?= e($listedProject['title']) ?></strong>
              <span><?= e($listedProject['role']) ?> · <?= $listedProject['is_published'] ? 'Published' : 'Draft' ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
