<?php
declare(strict_types=1);
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'cookie_path' => '/']);
$isAdminLoggedIn = isset($_SESSION['user_id']);
require __DIR__ . '/landing-content.php';
$storedLandingContent = [];
$landingVisibility = array_fill_keys(array_keys(landing_content_defaults()), true);
try {
  require __DIR__ . '/config.php';
  $contentRows = database()->query('SELECT section_key, content_json, is_visible FROM landing_content')->fetchAll(PDO::FETCH_ASSOC);
  foreach ($contentRows as $row) {
    $decoded = json_decode($row['content_json'], true);
    if (is_array($decoded)) $storedLandingContent[$row['section_key']] = $decoded;
    $landingVisibility[$row['section_key']] = (bool) $row['is_visible'];
  }
} catch (Throwable $error) {
  // Display built-in defaults if the CMS table is not yet configured.
}
$landing = landing_content($storedLandingContent);
function page_e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function section_class(string $section, array $visibility, bool $isAdmin): string { return !$visibility[$section] && $isAdmin ? ' admin-hidden-section' : ''; }
function has_section_content(array $section): bool {
  foreach ($section as $value) {
    if (is_array($value) && $value) return true;
    if (is_string($value) && trim($value) !== '') return true;
  }
  return false;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Game development portfolio for showcasing projects, roles, and playable work."
    />
    <title>Game Dev Portfolio</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <?php if ($isAdminLoggedIn || has_section_content($landing['site'])): ?><header class="site-header">
      <?php if ($landing['site']['brand']): ?><a class="brand" href="#top" aria-label="<?= page_e($landing['site']['brand']) ?> home"><?= page_e($landing['site']['brand']) ?></a><?php endif; ?>
      <nav aria-label="Primary navigation">
        <?php if ($landing['site']['nav_projects']): ?><a href="#projects"><?= page_e($landing['site']['nav_projects']) ?></a><?php endif; ?>
        <?php if ($landing['site']['nav_about']): ?><a href="#about"><?= page_e($landing['site']['nav_about']) ?></a><?php endif; ?>
        <?php if ($landing['site']['nav_contact']): ?><a href="#contact"><?= page_e($landing['site']['nav_contact']) ?></a><?php endif; ?>
        <a class="admin-nav-link" href="admin/<?= $isAdminLoggedIn ? 'index.php' : 'login.php' ?>"><?= $isAdminLoggedIn ? 'Admin dashboard' : 'Admin login' ?></a>
      </nav>
      <?php if ($isAdminLoggedIn): ?><a class="section-edit-link header-edit" href="admin/edit-landing-section.php?section=site">Edit</a><?php endif; ?>
    </header><?php endif; ?>

    <main id="top">
      <?php if ($isAdminLoggedIn || ($landingVisibility['hero'] && has_section_content($landing['hero']))): ?><section class="intro<?= section_class('hero', $landingVisibility, $isAdminLoggedIn) ?>" aria-labelledby="intro-title">
        <?php if ($isAdminLoggedIn): ?><a class="section-edit-link" href="admin/edit-landing-section.php?section=hero">Edit</a><?php endif; ?>
        <?php if ($landing['hero']['eyebrow']): ?><p class="eyebrow"><?= page_e($landing['hero']['eyebrow']) ?></p><?php endif; ?>
        <?php if ($landing['hero']['title']): ?><h1 id="intro-title"><?= page_e($landing['hero']['title']) ?></h1><?php endif; ?>
        <?php if ($landing['hero']['copy']): ?><p class="intro-copy"><?= page_e($landing['hero']['copy']) ?></p><?php endif; ?>
        <?php if ($landing['hero']['button_text']): ?><a class="button" href="#projects"><?= page_e($landing['hero']['button_text']) ?></a><?php endif; ?>
      </section><?php endif; ?>

      <?php if ($isAdminLoggedIn || $landingVisibility['projects']): ?><section id="projects" class="section<?= section_class('projects', $landingVisibility, $isAdminLoggedIn) ?>" aria-labelledby="projects-title">
        <div class="section-heading">
          <?php if ($landing['projects']['eyebrow']): ?><p class="eyebrow"><?= page_e($landing['projects']['eyebrow']) ?></p><?php endif; ?>
          <?php if ($landing['projects']['title'] || $isAdminLoggedIn): ?><div class="projects-heading-row"><?php if ($landing['projects']['title']): ?><h2 id="projects-title"><?= page_e($landing['projects']['title']) ?></h2><?php endif; ?><?php if ($isAdminLoggedIn): ?><span class="section-actions"><a class="section-edit-link" href="admin/edit-landing-section.php?section=projects">Edit</a><a class="add-project-link" href="admin/create-project.php">Add New Project</a></span><?php endif; ?></div><?php endif; ?>
        </div>
        <div id="project-list" class="project-grid" aria-live="polite" aria-busy="true">
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
        </div>
      </section><?php endif; ?>

      <?php if ($isAdminLoggedIn || ($landingVisibility['about'] && has_section_content($landing['about']))): ?><section id="about" class="section<?= section_class('about', $landingVisibility, $isAdminLoggedIn) ?>" aria-labelledby="about-title">
        <?php if ($landing['about']['eyebrow'] || $landing['about']['title'] || $isAdminLoggedIn): ?><div class="section-heading">
          <?php if ($isAdminLoggedIn): ?><a class="section-edit-link" href="admin/edit-landing-section.php?section=about">Edit</a><?php endif; ?>
          <?php if ($landing['about']['eyebrow']): ?><p class="eyebrow"><?= page_e($landing['about']['eyebrow']) ?></p><?php endif; ?>
          <?php if ($landing['about']['title']): ?><h2 id="about-title"><?= page_e($landing['about']['title']) ?></h2><?php endif; ?>
        </div><?php endif; ?>
        <?php if ($landing['about']['body']): ?><p><?= page_e($landing['about']['body']) ?></p><?php endif; ?>
      </section><?php endif; ?>

      <?php if ($isAdminLoggedIn || ($landingVisibility['contact'] && has_section_content($landing['contact']))): ?><section id="contact" class="section<?= section_class('contact', $landingVisibility, $isAdminLoggedIn) ?>" aria-labelledby="contact-title">
        <?php if ($landing['contact']['eyebrow'] || $landing['contact']['title'] || $isAdminLoggedIn): ?><div class="section-heading">
          <?php if ($isAdminLoggedIn): ?><a class="section-edit-link" href="admin/edit-landing-section.php?section=contact">Edit</a><?php endif; ?>
          <?php if ($landing['contact']['eyebrow']): ?><p class="eyebrow"><?= page_e($landing['contact']['eyebrow']) ?></p><?php endif; ?>
          <?php if ($landing['contact']['title']): ?><h2 id="contact-title"><?= page_e($landing['contact']['title']) ?></h2><?php endif; ?>
        </div><?php endif; ?>
        <div class="contact-details">
          <?php if ($landing['contact']['email']): ?><a class="contact-detail" href="mailto:<?= page_e($landing['contact']['email']) ?>"><span class="contact-icon" aria-hidden="true">✉</span><span>Email</span><strong><?= page_e($landing['contact']['email']) ?></strong></a><?php endif; ?>
          <?php if ($landing['contact']['phone']): ?><a class="contact-detail" href="tel:<?= page_e(preg_replace('/[^+0-9]/', '', $landing['contact']['phone'])) ?>"><span class="contact-icon" aria-hidden="true">☎</span><span>Phone</span><strong><?= page_e($landing['contact']['phone']) ?></strong></a><?php endif; ?>
          <?php if ($landing['contact']['linkedin_url']): ?><a class="contact-detail" href="<?= page_e($landing['contact']['linkedin_url']) ?>" target="_blank" rel="noopener"><span class="contact-icon contact-linkedin" aria-hidden="true">in</span><span>LinkedIn</span><strong>View profile ↗</strong></a><?php endif; ?>
          <?php if ($landing['contact']['github_url']): ?><a class="contact-detail" href="<?= page_e($landing['contact']['github_url']) ?>" target="_blank" rel="noopener"><span class="contact-icon" aria-hidden="true">GH</span><span>GitHub</span><strong>View profile ↗</strong></a><?php endif; ?>
          <?php if ($landing['contact']['x_url']): ?><a class="contact-detail" href="<?= page_e($landing['contact']['x_url']) ?>" target="_blank" rel="noopener"><span class="contact-icon" aria-hidden="true">𝕏</span><span>X</span><strong>View profile ↗</strong></a><?php endif; ?>
          <?php if ($landing['contact']['discord']): $discordIsUrl = filter_var($landing['contact']['discord'], FILTER_VALIDATE_URL); ?><<?= $discordIsUrl ? 'a' : 'div' ?> class="contact-detail"<?= $discordIsUrl ? ' href="' . page_e($landing['contact']['discord']) . '" target="_blank" rel="noopener"' : '' ?>><span class="contact-icon" aria-hidden="true">◉</span><span>Discord</span><strong><?= page_e($discordIsUrl ? 'Join server ↗' : $landing['contact']['discord']) ?></strong></<?= $discordIsUrl ? 'a' : 'div' ?>><?php endif; ?>
        </div>
        <?php if ($landing['contact']['custom']): ?><dl class="contact-custom">
          <?php foreach ($landing['contact']['custom'] as $item):
            $label = (string) ($item['label'] ?? '');
            $value = (string) ($item['value'] ?? '');
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
            $isWebLink = filter_var($value, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true);
            $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL);
            $isPhone = preg_match('/^[+0-9()\s.-]{5,}$/', $value);
            if (!$label || !$value) continue;
          ?>
            <div><dt><?= page_e($label) ?></dt><dd><?php if ($isWebLink): ?><a href="<?= page_e($value) ?>" target="_blank" rel="noopener"><?= page_e($value) ?></a><?php elseif ($isEmail): ?><a href="mailto:<?= page_e($value) ?>"><?= page_e($value) ?></a><?php elseif ($isPhone): ?><a href="tel:<?= page_e(preg_replace('/[^+0-9]/', '', $value)) ?>"><?= page_e($value) ?></a><?php else: ?><?= page_e($value) ?><?php endif; ?></dd></div>
          <?php endforeach; ?>
        </dl><?php endif; ?>
      </section><?php endif; ?>
    </main>

    <?php if ($isAdminLoggedIn || $landing['site']['footer_name']): ?><footer class="site-footer"><p>&copy; <span id="current-year"></span> <?= page_e($landing['site']['footer_name']) ?></p></footer><?php endif; ?>

    <script>window.isAdminLoggedIn = <?= $isAdminLoggedIn ? 'true' : 'false' ?>;</script>
    <script src="script.js"></script>
  </body>
</html>
