<?php
// Database connection with PDO.

// Returns ONE shared connection (it connects only the first time).
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        // The password is in config/db.php, outside public/.
        $c   = require __DIR__ . '/../config/db.php';
        $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}";
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // errors become exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows are arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements
        ]);
    }
    return $pdo;
}

// Runs ANY query as a prepared statement.
// The SQL has "?" holes. The values go in $params, never inside the SQL text.
// This is what stops SQL injection.
function run(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}