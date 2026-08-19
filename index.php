<?php
declare(strict_types=1);
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'cookie_path' => '/']);
$isAdminLoggedIn = isset($_SESSION['user_id']);
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
      <a class="brand" href="#top" aria-label="Game Dev Portfolio home">Game Dev Portfolio</a>
      <nav aria-label="Primary navigation">
        <a href="#projects">Projects</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <a class="admin-nav-link" href="admin/<?= $isAdminLoggedIn ? 'index.php' : 'login.php' ?>"><?= $isAdminLoggedIn ? 'Admin dashboard' : 'Admin login' ?></a>
      </nav>
    </header>

    <main id="top">
      <section class="intro" aria-labelledby="intro-title">
        <p class="eyebrow">Developer Portfolio</p>
        <h1 id="intro-title">Making playable worlds.</h1>
        <p class="intro-copy">
          A home for games, prototypes, and the systems that bring them to life.
        </p>
        <a class="button" href="#projects">View Projects</a>
      </section>

      <section id="projects" class="section" aria-labelledby="projects-title">
        <div class="section-heading">
          <p class="eyebrow">Selected Work</p>
          <div class="projects-heading-row"><h2 id="projects-title">Projects</h2><?php if ($isAdminLoggedIn): ?><a class="add-project-link" href="admin/create-project.php">Add New Project</a><?php endif; ?></div>
        </div>
        <div id="project-list" class="project-grid" aria-live="polite" aria-busy="true">
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
          <article class="project-card project-skeleton" aria-hidden="true"><div class="skeleton-image"></div><div class="project-card-content"><div class="skeleton-line short"></div><div class="skeleton-line title"></div><div class="skeleton-line"></div><div class="skeleton-line medium"></div></div></article>
        </div>
      </section>

      <section id="about" class="section" aria-labelledby="about-title">
        <div class="section-heading">
          <p class="eyebrow">About</p>
          <h2 id="about-title">The Developer</h2>
        </div>
        <p>
          Replace this short introduction with your development focus, tools, and the kind of games you build.
        </p>
      </section>

      <section id="contact" class="section" aria-labelledby="contact-title">
        <div class="section-heading">
          <p class="eyebrow">Contact</p>
          <h2 id="contact-title">Let&apos;s make something memorable.</h2>
        </div>
        <a class="text-link" href="mailto:hello@example.com">hello@example.com</a>
      </section>
    </main>

    <footer class="site-footer">
      <p>&copy; <span id="current-year"></span> Game Dev Portfolio</p>
    </footer>

    <script>window.isAdminLoggedIn = <?= $isAdminLoggedIn ? 'true' : 'false' ?>;</script>
    <script src="script.js"></script>
  </body>
</html>
