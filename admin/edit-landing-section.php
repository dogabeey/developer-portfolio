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
    'contact' => ['Contact section' => ['eyebrow' => 'Eyebrow', 'title' => 'Heading', 'email' => 'Email address', 'phone' => 'Phone number', 'linkedin_url' => 'LinkedIn URL', 'github_url' => 'GitHub URL', 'x_url' => 'X profile URL', 'discord' => 'Discord username or invite URL']],
];
$section = $_GET['section'] ?? '';
if (!isset($definitions[$section])) {
    http_response_code(404);
    exit('Section not found.');
}

$defaults = landing_content_defaults()[$section];
$statement = database()->prepare('SELECT content_json, is_visible FROM landing_content WHERE section_key = ?');
$statement->execute([$section]);
$storedRow = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$stored = json_decode((string) ($storedRow['content_json'] ?? ''), true);
$content = array_merge($defaults, is_array($stored) ? array_intersect_key($stored, $defaults) : []);
$isVisible = !isset($storedRow['is_visible']) || (bool) $storedRow['is_visible'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($definitions[$section][array_key_first($definitions[$section])] as $field => $_label) {
      $content[$field] = trim($_POST[$field] ?? '');
    }
    if ($section === 'contact') {
        $content['custom'] = [];
        $labels = $_POST['custom_label'] ?? [];
        $values = $_POST['custom_value'] ?? [];
        foreach ($labels as $index => $label) {
            $label = trim((string) $label);
            $value = trim((string) ($values[$index] ?? ''));
            if ($label !== '' && $value !== '' && count($content['custom']) < 12) {
                $content['custom'][] = ['label' => substr($label, 0, 80), 'value' => substr($value, 0, 2048)];
            }
        }
    }
    $isVisible = $section === 'site' || isset($_POST['is_visible']);
    if (isset($content['email']) && $content['email'] !== '' && !filter_var($content['email'], FILTER_VALIDATE_EMAIL)) {
        flash('Enter a valid email address.');
    } elseif ((($content['linkedin_url'] ?? '') && !filter_var($content['linkedin_url'], FILTER_VALIDATE_URL)) || (($content['github_url'] ?? '') && !filter_var($content['github_url'], FILTER_VALIDATE_URL)) || (($content['x_url'] ?? '') && !filter_var($content['x_url'], FILTER_VALIDATE_URL))) {
        flash('Enter valid LinkedIn, GitHub, and X URLs.');
    } else {
        $save = database()->prepare('INSERT INTO landing_content (section_key, content_json, is_visible) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE content_json = VALUES(content_json), is_visible = VALUES(is_visible)');
        $save->execute([$section, json_encode($content, JSON_THROW_ON_ERROR), $isVisible ? 1 : 0]);
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
        <?php if (in_array($field, ['copy', 'body'], true)): ?><textarea name="<?= e($field) ?>" rows="5"><?= e($content[$field]) ?></textarea><?php else: ?><input name="<?= e($field) ?>" value="<?= e($content[$field]) ?>" <?= $field === 'email' ? 'type="email"' : '' ?>><?php endif; ?>
      </label>
    <?php endforeach; ?>
    <?php if ($section === 'contact'): ?><div class="wide custom-contact-fields"><div class="custom-fields-heading"><strong>Custom contact details</strong><button class="secondary-button" type="button" id="add-contact-field">Add detail</button></div><p class="muted">Add Phone, LinkedIn, Itch.io, Discord, or any custom text.</p><div id="contact-field-list">
      <?php foreach ($content['custom'] as $item): ?><div class="custom-contact-row"><input name="custom_label[]" value="<?= e((string) ($item['label'] ?? '')) ?>" placeholder="Label (e.g. LinkedIn)"><input name="custom_value[]" value="<?= e((string) ($item['value'] ?? '')) ?>" placeholder="Value or URL"><button class="remove-contact-field" type="button">Remove</button></div><?php endforeach; ?>
    </div></div><?php endif; ?>
    <?php if ($section !== 'site'): ?><label class="check wide"><input type="checkbox" name="is_visible" value="1" <?= $isVisible ? 'checked' : '' ?>> Visible to public visitors</label><?php endif; ?>
    <div class="wide"><button type="submit">Save changes</button></div>
  </form></section></main>
  <?php if ($section === 'contact'): ?><script>
    const list = document.querySelector('#contact-field-list');
    document.querySelector('#add-contact-field').addEventListener('click', () => {
      const row = document.createElement('div');
      row.className = 'custom-contact-row';
      row.innerHTML = '<input name="custom_label[]" placeholder="Label (e.g. LinkedIn)"><input name="custom_value[]" placeholder="Value or URL"><button class="remove-contact-field" type="button">Remove</button>';
      list.append(row);
    });
    list.addEventListener('click', (event) => { if (event.target.classList.contains('remove-contact-field')) event.target.closest('.custom-contact-row').remove(); });
  </script><?php endif; ?>
</body>
</html>
