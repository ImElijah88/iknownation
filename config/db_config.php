<?php
/*
 * Database Configuration
 *
 * This file holds the database connection details for the Now Nation application.
 * It establishes a connection to the MySQL database and provides a connection
 * object ($conn) for other scripts to use.
 */

// --- DATABASE CREDENTIALS ---
// These are the default credentials for a local XAMPP/MAMP server.
// In a production environment, these should be stored securely.
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nownation";

// --- ESTABLISH CONNECTION ---
// Create a new mysqli object to connect to the database.
$conn = new mysqli($servername, $username, $password, $dbname);

// --- CONNECTION VALIDATION ---
// Check if the connection was successful. If not, terminate the script
// and display an error message. For a production app, this error
// would be logged to a file instead of being shown to the user.
if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// LLM API Key (Keep this secure!)
define('LLM_API_KEY', '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');

?>