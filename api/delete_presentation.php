<?php
/*
 * API: Delete Presentation
 * ------------------------
 * This script deletes a presentation from the database.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// --- Security Check ---
if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in to delete a presentation.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->id)) {
    $presentationId = (int)$data->id;
    $userId = getCurrentUser()['id'];

    // --- Permission Check ---
    // First, verify that the presentation belongs to the current user.
    $checkStmt = $conn->prepare("SELECT user_id FROM presentations WHERE id = ?");
    $checkStmt->bind_param("i", $presentationId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 1) {
        $presentation = $result->fetch_assoc();
        if ($presentation['user_id'] == $userId) {
            // --- Deletion Logic ---
            $deleteStmt = $conn->prepare("DELETE FROM presentations WHERE id = ?");
            $deleteStmt->bind_param("i", $presentationId);
            if ($deleteStmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Presentation deleted successfully.'];
            } else {
                $response['message'] = 'Failed to delete the presentation.';
            }
            $deleteStmt->close();
        } else {
            $response['message'] = 'You do not have permission to delete this presentation.';
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
