<?php
require_once 'src/config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Create Tables
    $commands = [
        "CREATE TABLE IF NOT EXISTS User (
            id TEXT PRIMARY KEY,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            name TEXT,
            role TEXT, -- Fixed from 'roles' to 'role' to match code expectations
            instrument TEXT,
            membershipNumber TEXT,
            image TEXT,
            mustChangePassword BOOLEAN DEFAULT 1,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS Role (
            id TEXT PRIMARY KEY,
            name TEXT UNIQUE NOT NULL,
            permissions TEXT NOT NULL, -- JSON string
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS _RoleToUser (
            A TEXT NOT NULL,
            B TEXT NOT NULL,
            FOREIGN KEY (A) REFERENCES Role(id) ON DELETE CASCADE,
            FOREIGN KEY (B) REFERENCES User(id) ON DELETE CASCADE,
            UNIQUE(A, B)
        )",
        "CREATE TABLE IF NOT EXISTS Event (
            id TEXT PRIMARY KEY,
            title TEXT NOT NULL,
            date DATETIME NOT NULL,
            location TEXT,
            description TEXT,
            isClosed BOOLEAN DEFAULT 0,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS Attendance (
            id TEXT PRIMARY KEY,
            userId TEXT NOT NULL,
            eventId TEXT NOT NULL,
            status TEXT NOT NULL,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userId) REFERENCES User(id),
            FOREIGN KEY (eventId) REFERENCES Event(id),
            UNIQUE(userId, eventId)
        )",
        "CREATE TABLE IF NOT EXISTS ClothingItem (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            image TEXT NOT NULL,
            userId TEXT NOT NULL,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userId) REFERENCES User(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS AttendanceHistory (
            id TEXT PRIMARY KEY,
            userId TEXT NOT NULL,
            eventId TEXT NOT NULL,
            status TEXT NOT NULL,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userId) REFERENCES User(id),
            FOREIGN KEY (eventId) REFERENCES Event(id)
        )",
        "CREATE TABLE IF NOT EXISTS ProfileChangeRequest (
            id TEXT PRIMARY KEY,
            userId TEXT NOT NULL,
            newName TEXT,
            newEmail TEXT,
            newInstrument TEXT,
            newImage TEXT,
            status TEXT DEFAULT 'PENDING',
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userId) REFERENCES User(id)
        )"

    ];

    foreach ($commands as $command) {
        $db->exec($command);
    }
    echo "Tables created successfully.<br>";

    // 2. Seed Data
    // Check if admin exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM User WHERE email = ?");
    $stmt->execute(['admin@example.com']);

    if ($stmt->fetchColumn() == 0) {
        // Create Admin Role
        $roleId = uniqid('role_');
        $permissions = json_encode(['VIEW_ADMIN', 'MANAGE_USERS', 'MANAGE_ROLES', 'MANAGE_EVENTS', 'VIEW_ATTENDANCE']);

        $stmt = $db->prepare("INSERT INTO Role (id, name, permissions) VALUES (?, ?, ?)");
        $stmt->execute([$roleId, 'ADMIN', $permissions]);

        // Create Admin User
        $userId = uniqid('user_');
        $password = password_hash('admin123', PASSWORD_DEFAULT); // Using PHP default bcrypt

        // FIX: Insert 'ADMIN' into 'role' column so simple checks work
        $stmt = $db->prepare("INSERT INTO User (id, email, password, name, role, mustChangePassword) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$userId, 'admin@example.com', $password, 'Admin User', 'ADMIN']);

        // Link Role to User (in _RoleToUser pivot table)
        $stmt = $db->prepare("INSERT INTO _RoleToUser (A, B) VALUES (?, ?)");
        $stmt->execute([$roleId, $userId]);

        echo "Admin user and role seeded.<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
