<?php
// ── Database Configuration ──────────────────────────────
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "hr_it_support";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Ensure users table exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(255)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;");

// Ensure tickets table has a status column (safe ALTER)
$conn->query("ALTER TABLE tickets ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'Open'");

// ── Helper functions ────────────────────────────────────

function sanitize($input) {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Returns the CSS badge class for a given priority level.
 * Matches the .badge-* classes in styles.css.
 */
function get_priority_class($priority) {
    return match(strtolower(trim($priority ?? ''))) {
        'low'      => 'badge-low',
        'medium'   => 'badge-medium',
        'high'     => 'badge-high',
        'critical' => 'badge-critical',
        default    => ''
    };
}

/**
 * Returns the CSS badge class for a given ticket status.
 * Matches the .badge-* classes in styles.css.
 */
function get_status_class($status) {
    return match(strtolower(trim($status ?? ''))) {
        'open'                 => 'badge-open',
        'in-progress'          => 'badge-in-progress',
        'for parts'            => 'badge-parts',
        'for replacement'      => 'badge-replacement',
        'endorsed to supplier' => 'badge-supplier',
        'unrepairable'         => 'badge-unrepairable',
        'resolved'             => 'badge-resolved',
        'closed'               => 'badge-closed',
        default                => 'badge-open'
    };
}