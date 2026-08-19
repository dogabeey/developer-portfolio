<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $statement = database()->prepare('DELETE FROM projects WHERE id = ?');
    $statement->execute([(int) ($_POST['id'] ?? 0)]);
    flash('Project deleted.');
}
header('Location: index.php');
