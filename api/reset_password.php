<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];
$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->action) && $data->action === 'reset_password') {
    if (!empty($data->token) && !empty($data->new_password)) {
        
        $hashed_token = hash('sha256', $data->token);

        // Find user by token and check expiry
        $stmt = $conn->prepare("SELECT * FROM users WHERE password_reset_token = ?");
        $stmt->bind_param("s", $hashed_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if the token has expired
            if (new DateTime() < new DateTime($user['password_reset_expiry'])) {
                
                // Token is valid, update the password
                $new_hashed_password = password_hash($data->new_password, PASSWORD_DEFAULT);

                // Invalidate the token and update password
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expiry = NULL WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user['id']);
                
                if ($update_stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Your password has been reset successfully! You can now log in.'];
                } else {
                    $response['message'] = 'Failed to update your password. Please try again.';
                }
                $update_stmt->close();

            } else {
                $response['message'] = 'This password reset link has expired. Please request a new one.';
            }
        } else {
            $response['message'] = 'Invalid or expired password reset link.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'A token and a new password are required.';
    }
}

$conn->close();
echo json_encode($response);
?>