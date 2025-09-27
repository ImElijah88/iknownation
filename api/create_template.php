<?php
/*
 * API: Create Template
 * ----------------------
 * This script handles the creation of new presentation templates by an admin.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// --- Security Check ---
if (!isAdmin()) {
    $response['message'] = 'You do not have permission to create templates.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->template_name) && isset($data->description) && isset($data->features)) {
    $templateName = $conn->real_escape_string($data->template_name);
    $description = $conn->real_escape_string($data->description);
    
    // The features are expected to be a JSON object from the front-end.
    $featuresJson = json_encode($data->features);

    // --- Placeholder for LLM Interaction ---
    // In a real implementation, the $featuresJson would be sent to an LLM
    // to generate a more complex structure (e.g., slide layouts, style rules).
    // For now, we are just saving the features the admin selected.

    // --- Database Insertion ---
    $stmt = $conn->prepare("INSERT INTO templates (template_name, description, features) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $templateName, $description, $featuresJson);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Template created successfully!'];
    } else {
        $response['message'] = 'Failed to save the template.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Missing required template data.';
}

$conn->close();
echo json_encode($response);
?>
