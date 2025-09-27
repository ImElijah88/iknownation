<?php
/*
 * API: Get All Templates
 * ----------------------
 * This script fetches all available templates from the database.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

$response = ['status' => 'error', 'message' => 'No templates found.'];

$stmt = $conn->prepare("SELECT id, template_name, description, features FROM templates");
$stmt->execute();
$result = $stmt->get_result();

$templates = [];
while ($row = $result->fetch_assoc()) {
    // Decode the features JSON string into a PHP array/object
    $row['features'] = json_decode($row['features'], true);
    $templates[] = $row;
}

if (!empty($templates)) {
    $response = ['status' => 'success', 'templates' => $templates];
} else {
    $response['message'] = 'No templates available.';
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>
