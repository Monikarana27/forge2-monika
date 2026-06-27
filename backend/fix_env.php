<?php
require __DIR__.'/vendor/autoload.php';

$envFile = __DIR__.'/.env';
$envContent = file_get_contents($envFile);

// Replace the password line with the exact password
$newContent = preg_replace(
    '/DB_PASSWORD=.*/',
    'DB_PASSWORD=NewPassword123!',
    $envContent
);

file_put_contents($envFile, $newContent);

echo ".env file updated successfully\n";
echo "Testing database connection...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pulsedesk', 'root', 'NewPassword123!');
    echo "Database connection successful!\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}