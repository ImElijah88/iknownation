<?php
/*
 * API: Get Template Details
 * -------------------------
 * This script fetches details for a specific template, including its features.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

if (isset($_GET['template_id'])) {
    $templateId = (int)$_GET['template_id'];

    $stmt = $conn->prepare("SELECT id, template_name, description, features FROM templates WHERE id = ?");
    $stmt->bind_param("i", $templateId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $template = $result->fetch_assoc();
        // Decode the features JSON string into a PHP array/object
        $template['features'] = json_decode($template['features'], true);
        $response = ['status' => 'success', 'template' => $template];
    } else {
        $response['message'] = 'Template not found.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Template ID not provided.';
}

$conn->close();
echo json_encode($response);
?>
