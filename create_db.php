<?php
$configs = [
    ['host' => '127.0.0.1', 'port' => 3307, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
];

foreach ($configs as $config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']}";
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS wanderjournal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        echo "SUCCESS with {$config['host']}:{$config['port']} user={$config['user']}\n";
        echo "Database 'wanderjournal' created/verified!\n";
        exit(0);
    } catch (Exception $e) {
        echo "FAILED {$config['host']}:{$config['port']} - " . $e->getMessage() . "\n";
    }
}
echo "\nAll attempts failed. MySQL may not be running or needs a password.\n";
