<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$projects = database()->query('SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$message = flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Projects | Game Dev Portfolio</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="admin-header">
    <div><p class="eyebrow">Game Dev Portfolio</p><h1>Projects</h1></div>
    <nav><a href="../index.php" target="_blank">View portfolio ↗</a><span><?= e($_SESSION['username']) ?></span><a href="logout.php">Log out</a></nav>
  </header>
  <main class="admin-main">
    <?php if ($message): ?><p class="notice success"><?= e($message) ?></p><?php endif; ?>
    <section class="panel" aria-labelledby="add-project"><h2 id="add-project">Add a project</h2>
      <form class="project-form" method="post" action="save-project.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Manual title <small>(used as fallback when store metadata is unavailable)</small><input name="title" maxlength="160" required></label>
        <label>Role<input name="role" maxlength="160" placeholder="Gameplay Programming" required></label>
        <label class="wide">Manual description <small>(optional fallback when store metadata is unavailable)</small><textarea name="description" rows="4"></textarea><span class="metadata-toggle"><input type="checkbox" name="use_metadata_description" value="1" checked> Use store metadata for title, description, and thumbnail</span></label>
        <label>Project link <small>(optional)</small><input type="url" name="project_url" maxlength="2048" placeholder="https://..."></label>
        <label>Manual thumbnail URL <small>(optional fallback)</small><input type="url" name="thumbnail_url" maxlength="2048" placeholder="https://.../cover.jpg"></label>
        <label class="wide">Manual screenshot URLs <small>(optional fallback, one URL per line)</small><textarea name="screenshot_urls" rows="4" placeholder="https://.../screenshot-1.jpg&#10;https://.../screenshot-2.jpg"></textarea><span class="metadata-toggle"><input type="checkbox" name="use_metadata_screenshots" value="1" checked> Use store metadata for screenshots</span></label>
        <label>Display order<input type="number" name="sort_order" value="0"></label>
        <label class="check wide"><input type="checkbox" name="is_published" value="1" checked> Publish on portfolio immediately</label>
        <div class="wide"><button type="submit">Add project</button></div>
      </form>
    </section>
    <section class="panel" aria-labelledby="existing-projects"><h2 id="existing-projects">Existing projects</h2>
      <?php if (!$projects): ?><p class="muted">Your first project will appear here.</p><?php else: ?>
        <div class="project-table-wrap"><table><thead><tr><th>Project</th><th>Order</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($projects as $project): ?><tr>
          <td><strong><?= e($project['title']) ?></strong><br><span><?= e($project['role']) ?></span></td>
          <td><?= (int) $project['sort_order'] ?></td><td><?= $project['is_published'] ? 'Published' : 'Draft' ?></td>
          <td class="project-actions"><a href="edit-project.php?id=<?= (int) $project['id'] ?>">Edit</a><form method="post" action="delete-project.php" onsubmit="return confirm('Delete this project?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $project['id'] ?>"><button class="danger" type="submit">Delete</button></form></td>
        </tr><?php endforeach; ?></tbody></table></div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
