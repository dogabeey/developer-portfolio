<?php
// Copy this file to config.php and enter your local MySQL credentials.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'game_dev_portfolio';
const DB_USER = 'root';
const DB_PASS = '';
// Generate a long random value for each environment; used to hash click IPs.
const CLICK_HASH_SALT = 'replace-with-a-long-random-secret';

function database(): PDO
{
    static $connection = null;

    if ($connection === null) {
        $connection = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    return $connection;
}
