<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add project | Game Dev Portfolio</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="admin-header">
    <div><p class="eyebrow">Game Dev Portfolio</p><h1>Add a project</h1></div>
    <nav><a href="index.php">← Back to projects</a><a href="logout.php">Log out</a></nav>
  </header>
  <main class="admin-main">
    <section class="panel">
      <form class="project-form" method="post" action="save-project.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Manual title <small>(used as fallback when store metadata is unavailable)</small><input name="title" maxlength="160" required autofocus></label>
        <label>Role<input name="role" maxlength="160" placeholder="Gameplay Programming" required></label>
        <label class="wide">Manual description <small>(optional fallback when store metadata is unavailable)</small><textarea name="description" rows="4"></textarea><span class="metadata-toggle"><input type="checkbox" name="use_metadata_description" value="1"> Use store metadata for title, description, and thumbnail</span></label>
        <label>Project link <small>(optional)</small><input type="url" name="project_url" maxlength="2048" placeholder="https://..."></label>
        <label>Manual thumbnail URL <small>(optional fallback)</small><input type="url" name="thumbnail_url" maxlength="2048" placeholder="https://.../cover.jpg"></label>
        <label class="wide">Manual screenshot URLs <small>(optional fallback, one URL per line)</small><textarea name="screenshot_urls" rows="4" placeholder="https://.../screenshot-1.jpg&#10;https://.../screenshot-2.jpg"></textarea><span class="metadata-toggle"><input type="checkbox" name="use_metadata_screenshots" value="1"> Use store metadata for screenshots</span></label>
        <label>Display order<input type="number" name="sort_order" value="0"></label>
        <label class="check wide"><input type="checkbox" name="is_published" value="1" checked> Publish on portfolio immediately</label>
        <div class="wide"><button type="submit">Add project</button></div>
      </form>
    </section>
  </main>
</body>
</html>
