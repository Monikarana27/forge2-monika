<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Test different password formats
    $passwords = ['***', 'NewPassword123!', ''];
    
    foreach ($passwords as $pwd) {
        try {
            echo "Testing with password: '$pwd'\n";
            $pdo = new PDO('mysql:host=127.0.0.1', 'root', $pwd);
            echo "SUCCESS with password: '$pwd'\n";
            $stmt = $pdo->query("SELECT 'Database connection successful' as message");
            $result = $stmt->fetch();
            echo "Message: " . $result['message'] . "\n";
            break;
        } catch (PDOException $e) {
            echo "FAILED with password '$pwd': " . $e->getMessage() . "\n\n";
        }
    }
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}