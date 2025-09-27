<?php
/*
 * API: Get Presentations
 * ----------------------
 * This script fetches presentation data from the database and returns it as JSON.
 * It handles requests for both guest users and logged-in users.
 */

// Set the content type to JSON for all responses.
header('Content-Type: application/json');

// Include the database configuration and session manager.
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$userId = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
}

$presentations = [];

// Prepare the SQL statement.
// If a user is logged in, fetch their presentations AND the default ones (user_id IS NULL).
// If they are a guest, fetch only the default ones.
$sql = "SELECT p.id, p.user_id, p.presentation_key, p.title, p.slides, p.quizzes, p.speech, p.colors, p.status, t.template_name FROM presentations p JOIN templates t ON p.template_id = t.id WHERE p.user_id IS NULL";

if ($userId) {
    $sql .= " OR p.user_id = ?";
}

$stmt = $conn->prepare($sql);

if ($userId) {
    $stmt->bind_param("i", $userId);
}

// Execute the query and process the results.
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields into arrays/objects
        $row['slides'] = json_decode($row['slides'], true);
        $row['quizzes'] = json_decode($row['quizzes'], true);
        $row['colors'] = json_decode($row['colors'], true);

        // Use presentation_key as the key in the final JSON output
        $presentations[$row['presentation_key']] = $row;
    }
}

$stmt->close();
$conn->close();

// Return the final presentations object as JSON.
echo json_encode($presentations);

?>
