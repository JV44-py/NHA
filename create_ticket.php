<?php
// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "hr_it_support";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Ensure users table exists for authentication
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;");

// Helper functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function get_priority_class($priority) {
    switch(strtolower($priority)) {
        case 'low':
            return 'priority-low';
        case 'medium':
            return 'priority-medium';
        case 'high':
            return 'priority-high';
        default:
            return '';
    }
}

function get_status_class($status) {
    switch(strtolower($status)) {
        case 'open':
            return 'status-open';
        case 'in-progress':
            return 'status-in-progress';
        case 'resolved':
            return 'status-resolved';
        default:
            return '';
    }
}
?>
