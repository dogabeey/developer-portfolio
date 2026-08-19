<?php
declare(strict_types=1);

// Run once from a terminal: php create-admin.php your-username your-password
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php create-admin.php <username> <password>\n");
    exit(1);
}

require __DIR__ . '/config.php';

$statement = database()->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
$statement->execute([$argv[1], password_hash($argv[2], PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Admin account created.\n");
