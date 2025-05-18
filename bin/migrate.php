<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$serverPdo = $database->getServerConnection();

try {
    // Db creation
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `annotatex` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ Database 'annotatex' ensured\n";

    // Connection
    $pdo = $database->getConnection();

    // Table creation
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        avatar VARCHAR(255) DEFAULT 'default_avatar.png',
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE
    );

    CREATE TABLE IF NOT EXISTS images (
        image_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image_url VARCHAR(255) NOT NULL,
        visibility BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS annotations (
        annotation_id INT AUTO_INCREMENT PRIMARY KEY,
        image_id INT NOT NULL,
        user_id INT NOT NULL,
        x INT NOT NULL,
        y INT NOT NULL,
        width INT NOT NULL,
        height INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (image_id) REFERENCES images(image_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );
    ");

    echo "✅ Tables created\n";

    // Insert test data
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

    $pdo->exec("
    INSERT INTO users (username, email, password)
    VALUES ('testuser', 'test@example.com', '$passwordHash');

    INSERT INTO images (user_id, username, visibility, title, description, image_url)
    VALUES (1, 'testuser', TRUE, 'test_title', 'test_description', 'image_placeholder.webp');

    INSERT INTO annotations (image_id, user_id, x, y, width, height, comment)
    VALUES (1, 1, 100, 100, 50, 50, 'Example Label');
    ");

    echo "✅ Test data inserted\n";

} catch (PDOException $e) {
    die("❌ Error during migration: " . $e->getMessage());
} finally {
    // Close the connection
    $database->closeConnection();
}