<?php
/*
 * API: Save Presentation
 * ------------------------
 * This script updates a presentation's status from 'temporary' to 'saved'.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// --- Security & Permission Checks ---
if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in to save a presentation.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->id)) {
    $presentationId = (int)$data->id;
    $userId = getCurrentUser()['id'];

    // Verify that the presentation belongs to the current user before updating.
    $checkStmt = $conn->prepare("SELECT user_id FROM presentations WHERE id = ?");
    $checkStmt->bind_param("i", $presentationId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 1) {
        $presentation = $result->fetch_assoc();
        if ($presentation['user_id'] == $userId) {
            // --- Update Logic ---
            $updateStmt = $conn->prepare("UPDATE presentations SET status = 'saved' WHERE id = ?");
            $updateStmt->bind_param("i", $presentationId);
            if ($updateStmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Presentation saved successfully!'];
            } else {
                $response['message'] = 'Failed to save the presentation.';
            }
            $updateStmt->close();
        } else {
            $response['message'] = 'You do not have permission to save this presentation.';
        }
    } else {
        $response['message'] = 'Presentation not found.';
    }
    $checkStmt->close();
} else {
    $response['message'] = 'Presentation ID not provided.';
}

$conn->close();
echo json_encode($response);
?>
