<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Unauthorized'];

if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];

    $stmt = $conn->prepare("SELECT email, progress_data FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $totalXp = 0;
        $badges = []; // Placeholder for badges logic

        if ($user['progress_data']) {
            $progressData = json_decode($user['progress_data'], true);
            if (is_array($progressData)) {
                foreach ($progressData as $presentationProgress) {
                    if (isset($presentationProgress['xp'])) {
                        $totalXp += (int)$presentationProgress['xp'];
                    }
                }
            }
        }

        $response = [
            'status' => 'success',
            'user' => [
                'email' => $user['email'],
                'total_xp' => $totalXp,
                'badges' => $badges
            ]
        ];
    } else {
        $response['message'] = 'User not found.';
    }
    $stmt->close();
} else {
    http_response_code(401);
}

$conn->close();
echo json_encode($response);
?>