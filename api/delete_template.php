<?php
/*
 * API: Delete Template
 * ----------------------
 * This script handles the deletion of a presentation template by an admin.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// --- Security Check ---
if (!hasRole('admin')) {
    $response['message'] = 'You do not have permission to delete templates.';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->id)) {
    $templateId = (int)$data->id;

    // --- Database Deletion ---
    $stmt = $conn->prepare("DELETE FROM templates WHERE id = ?");
    $stmt->bind_param("i", $templateId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = ['status' => 'success', 'message' => 'Template deleted successfully!'];
        } else {
            $response['message'] = 'Template not found or already deleted.';
            http_response_code(404);
        }
    } else {
        $response['message'] = 'Failed to delete the template.';
        http_response_code(500);
    }
    $stmt->close();
} else {
    $response['message'] = 'Missing template ID.';
    http_response_code(400);
}

$conn->close();
echo json_encode($response);
?>
