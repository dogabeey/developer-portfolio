<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require dirname(__DIR__) . '/landing-content.php';
require_login();

$definitions = [
    'site' => ['Site identity' => ['brand' => 'Site name', 'nav_projects' => 'Projects navigation label', 'nav_about' => 'About navigation label', 'nav_contact' => 'Contact navigation label', 'footer_name' => 'Footer name']],
    'hero' => ['Hero section' => ['eyebrow' => 'Eyebrow', 'title' => 'Heading', 'copy' => 'Introduction', 'button_text' => 'Button label']],
    'projects' => ['Projects section' => ['eyebrow' => 'Eyebrow', 'title' => 'Heading']],
    'about' => ['About section' => ['eyebrow' => 'Eyebrow', 'title' => 'Heading', 'body' => 'Body text']],
    'contact' => ['Contact section' => ['eyebrow' => 'Eyebrow', 'title' => 'Heading', 'email' => 'Email address']],
];
$section = $_GET['section'] ?? '';
if (!isset($definitions[$section])) {
    http_response_code(404);
    exit('Section not found.');
}

$defaults = landing_content_defaults()[$section];
$statement = database()->prepare('SELECT content_json FROM landing_content WHERE section_key = ?');
$statement->execute([$section]);
$stored = json_decode((string) $statement->fetchColumn(), true);
$content = array_merge($defaults, is_array($stored) ? array_intersect_key($stored, $defaults) : []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($definitions[$section][array_key_first($definitions[$section])] as $field => $_label) {
        $content[$field] = trim($_POST[$field] ?? '');
    }
    if (isset($content['email']) && $content['email'] !== '' && !filter_var($content['email'], FILTER_VALIDATE_EMAIL)) {
        flash('Enter a valid email address.');
    } else {
        $save = database()->prepare('INSERT INTO landing_content (section_key, content_json) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_json = VALUES(content_json)');
        $save->execute([$section, json_encode($content, JSON_THROW_ON_ERROR)]);
        flash('Landing page section updated.');
        header('Location: ../index.php#' . ($section === 'hero' || $section === 'site' ? 'top' : $section));
        exit;
    }
}

$heading = array_key_first($definitions[$section]);
$fields = $definitions[$section][$heading];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit <?= e($heading) ?> | Game Dev Portfolio</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="admin-header"><div><p class="eyebrow">Landing page editor</p><h1><?= e($heading) ?></h1></div><nav><a href="../index.php">← Back to site</a><a href="logout.php">Log out</a></nav></header>
  <main class="admin-main"><section class="panel"><form class="project-form" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <?php foreach ($fields as $field => $label): ?>
      <label class="<?= in_array($field, ['copy', 'body'], true) ? 'wide' : '' ?>"><?= e($label) ?>
        <?php if (in_array($field, ['copy', 'body'], true)): ?><textarea name="<?= e($field) ?>" rows="5"><?= e($content[$field]) ?></textarea><?php else: ?><input name="<?= e($field) ?>" value="<?= e($content[$field]) ?>" <?= $field === 'email' ? 'type="email"' : '' ?> required><?php endif; ?>
      </label>
    <?php endforeach; ?>
    <div class="wide"><button type="submit">Save changes</button></div>
  </form></section></main>
</body>
</html>
