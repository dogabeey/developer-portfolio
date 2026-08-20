<?php
declare(strict_types=1);
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'cookie_path' => '/']);
$isAdminLoggedIn = isset($_SESSION['user_id']);
require __DIR__ . '/landing-content.php';
$storedLandingContent = [];
try {
  require __DIR__ . '/config.php';
  $contentRows = database()->query('SELECT section_key, content_json FROM landing_content')->fetchAll(PDO::FETCH_ASSOC);
  foreach ($contentRows as $row) {
    $decoded = json_decode($row['content_json'], true);
    if (is_array($decoded)) $storedLandingContent[$row['section_key']] = $decoded;
  }
} catch (Throwable $error) {
  // Display built-in defaults if the CMS table is not yet configured.
}
$landing = landing_content($storedLandingContent);
function page_e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
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
    <header class="site-header">
      <a class="brand" href="#top" aria-label="<?= page_e($landing['site']['brand']) ?> home"><?= page_e($landing['site']['brand']) ?></a>
      <nav aria-label="Primary navigation">
        <a href="#projects"><?= page_e($landing['site']['nav_projects']) ?></a>
        <a href="#about"><?= page_e($landing['site']['nav_about']) ?></a>
        <a href="#contact"><?= page_e($landing['site']['nav_contact']) ?></a>
        <a class="admin-nav-link" href="admin/<?= $isAdminLoggedIn ? 'index.php' : 'login.php' ?>"><?= $isAdminLoggedIn ? 'Admin dashboard' : 'Admin login' ?></a>
      </nav>
      <?php if ($isAdminLoggedIn): ?><a class="section-edit-link header-edit" href="admin/edit-landing-section.php?section=site">Edit</a><?php endif; ?>
    </header>

    <main id="top">
      <section class="intro" aria-labelledby="intro-title">
        <?php if ($isAdminLoggedIn): ?><a class="section-edit-link" href="admin/edit-landing-section.php?section=hero">Edit</a><?php endif; ?>
        <p class="eyebrow"><?= page_e($landing['hero']['eyebrow']) ?></p>
        <h1 id="intro-title"><?= page_e($landing['hero']['title']) ?></h1>
        <p class="intro-copy"><?= page_e($landing['hero']['copy']) ?></p>
        <a class="button" href="#projects"><?= page_e($landing['hero']['button_text']) ?></a>
      </section>

      <section id="projects" class="section" aria-labelledby="projects-title">
        <div class="section-heading">
          <p class="eyebrow"><?= page_e($landing['projects']['eyebrow']) ?></p>
          <div class="projects-heading-row"><h2 id="projects-title"><?= page_e($landing['projects']['title']) ?></h2><?php if ($isAdminLoggedIn): ?><span class="section-actions"><a class="section-edit-link" href="admin/edit-landing-section.php?section=projects">Edit</a><a class="add-project-link" href="admin/create-project.php">Add New Project</a></span><?php endif; ?></div>
        </div>
        <div id="project-list" class="project-grid" aria-live="polite" aria-busy="true">
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
        </div>
      </section>

      <section id="about" class="section" aria-labelledby="about-title">
        <div class="section-heading">
          <?php if ($isAdminLoggedIn): ?><a class="section-edit-link" href="admin/edit-landing-section.php?section=about">Edit</a><?php endif; ?>
          <p class="eyebrow"><?= page_e($landing['about']['eyebrow']) ?></p>
          <h2 id="about-title"><?= page_e($landing['about']['title']) ?></h2>
        </div>
        <p>
          <?= page_e($landing['about']['body']) ?>
        </p>
      </section>

      <section id="contact" class="section" aria-labelledby="contact-title">
        <div class="section-heading">
          <?php if ($isAdminLoggedIn): ?><a class="section-edit-link" href="admin/edit-landing-section.php?section=contact">Edit</a><?php endif; ?>
          <p class="eyebrow"><?= page_e($landing['contact']['eyebrow']) ?></p>
          <h2 id="contact-title"><?= page_e($landing['contact']['title']) ?></h2>
        </div>
        <a class="text-link" href="mailto:<?= page_e($landing['contact']['email']) ?>"><?= page_e($landing['contact']['email']) ?></a>
      </section>
    </main>

    <footer class="site-footer">
      <p>&copy; <span id="current-year"></span> <?= page_e($landing['site']['footer_name']) ?></p>
    </footer>

    <script>window.isAdminLoggedIn = <?= $isAdminLoggedIn ? 'true' : 'false' ?>;</script>
    <script src="script.js"></script>
  </body>
</html>
