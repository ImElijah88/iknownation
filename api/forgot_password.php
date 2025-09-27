<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];
$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->action) && $data->action === 'request_reset') {
    if (!empty($data->email)) {
        $email = $conn->real_escape_string($data->email);
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $hashed_token = hash('sha256', $token);
            $expiry_date = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

            // Store the token and expiry in the database
            $update_stmt = $conn->prepare("UPDATE users SET password_reset_token = ?, password_reset_expiry = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $hashed_token, $expiry_date, $user['id']);
            $update_stmt->execute();
            $update_stmt->close();

            // --- Simulate sending an email ---
            $reset_link = 'http://localhost/videoideas/Nownationproject/reset_password.php?token=' . $token;
            $email_content = "Hello,\n\nSomeone has requested a password reset for your account.\nIf this was you, please click the following link to reset your password:\n" . $reset_link . "\n\nIf you did not request this, you can safely ignore this email.\n";
            
            // For demonstration purposes, we'll save this to a file instead of sending an email.
            file_put_contents('../password_reset_links.txt', $email_content, FILE_APPEND);

            $response = ['status' => 'success', 'message' => 'If an account with that email exists, a password recovery link has been sent.'];
        } else {
            // We give a generic message to prevent user enumeration
            $response = ['status' => 'success', 'message' => 'If an account with that email exists, a password recovery link has been sent.'];
        }
        $stmt->close();
    } else {
        $response['message'] = 'Email is required.';
    }
}

$conn->close();
echo json_encode($response);
?>