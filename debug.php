<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Info</h1>";

echo "<h2>Paths</h2>";
echo "Current File: " . __FILE__ . "<br>";
echo "Current Dir: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

echo "<h2>Database Check</h2>";
$dbPath = __DIR__ . '/database.sqlite';
echo "Expected DB Path (relative to root): " . $dbPath . "<br>";

if (file_exists($dbPath)) {
    echo "Database file exists.<br>";
    if (is_writable($dbPath)) {
        echo "Database file is writable.<br>";
    } else {
        echo "<strong>ERROR: Database file is NOT writable.</strong><br>";
    }
} else {
    echo "<strong>ERROR: Database file does not exist. Run setup_db.php</strong><br>";
}

echo "<h2>Connection Test</h2>";
try {
    require_once 'src/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    if ($conn) {
        echo "Database Connection Successful.<br>";

        $stmt = $conn->query("SELECT count(*) FROM User");
        $count = $stmt->fetchColumn();
        echo "User count: " . $count . "<br>";
    } else {
        echo "<strong>Connection returned null.</strong><br>";
    }
} catch (Exception $e) {
    echo "<strong>Connection Exception: " . $e->getMessage() . "</strong><br>";
}

echo "<h2>Session Test</h2>";
session_start();
$_SESSION['test'] = 'working';
echo "Session set. Refresh to see if it persists (check cookies).<br>";
print_r($_SESSION);
