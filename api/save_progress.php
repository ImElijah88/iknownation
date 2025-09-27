<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in to save your progress.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['presentation_key'], $data['current_slide'], $data['xp'])) {
    $userId = getCurrentUser()['id'];
    $presentationKey = $data['presentation_key'];
    $currentSlide = (int)$data['current_slide'];
    $xp = (int)$data['xp'];

    try {
        // 1. Fetch current progress data
        $stmt = $conn->prepare("SELECT progress_data FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $progressData = $row['progress_data'] ? json_decode($row['progress_data'], true) : [];

        // 2. Update the progress for the specific presentation
        $progressData[$presentationKey] = [
            'slide' => $currentSlide,
            'xp' => $xp
        ];

        // 3. Save the updated progress data back to the database
        $updatedProgressJson = json_encode($progressData);
        $stmt = $conn->prepare("UPDATE users SET progress_data = ? WHERE id = ?");
        $stmt->bind_param("si", $updatedProgressJson, $userId);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Progress saved successfully.'];
        } else {
            $response['message'] = 'Failed to save progress.';
        }
        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = 'Database Error: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Missing required progress data.';
}

$conn->close();
echo json_encode($response);
?>