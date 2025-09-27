<?php
/*
 * API: User Authentication
 * -------------------------
 * This script handles user registration and login.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];
$data = json_decode(file_get_contents('php://input'));

if ($data && isset($data->action)) {
    switch ($data->action) {
        case 'register':
            if (!empty($data->email) && !empty($data->password)) {
                $email = $conn->real_escape_string($data->email);
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $response['message'] = 'An account with this email already exists.';
                } else {
                    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
                    $insert_stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                    $insert_stmt->bind_param("ss", $email, $hashed_password);
                    if ($insert_stmt->execute()) {
                        $response = ['status' => 'success', 'message' => 'Registration successful! Please log in.'];
                    } else {
                        $response['message'] = 'Registration failed. Please try again.';
                    }
                    $insert_stmt->close();
                }
                $stmt->close();
            } else {
                $response['message'] = 'Email and password are required.';
            }
            break;

        case 'login':
            if (!empty($data->email) && !empty($data->password)) {
                $email = $conn->real_escape_string($data->email);
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($data->password, $user['password'])) {
                        loginUser($user); // Use the function from session_manager

                        // --- Remember Me Logic ---
                        if (isset($data->remember) && $data->remember === true) {
                            $token = bin2hex(random_bytes(32));
                            $hashed_token = hash('sha256', $token);
                            $expiry_date = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

                            $update_stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_token_expiry = ? WHERE id = ?");
                            $update_stmt->bind_param("ssi", $hashed_token, $expiry_date, $user['id']);
                            $update_stmt->execute();
                            $update_stmt->close();

                            // Set the cookie on the client
                            $cookie_name = 'remember_me_token';
                            $cookie_value = $user['id'] . ':' . $token;
                            $cookie_expiry = time() + (86400 * 30);
                            // In a production environment, the 'secure' flag should be true and samesite='Strict'
                            setcookie($cookie_name, $cookie_value, $cookie_expiry, "/", "", false, true);
                        }

                        $response = ['status' => 'success', 'message' => 'Login successful!', 'user' => getCurrentUser()];
                    } else {
                        $response['message'] = 'Incorrect password.';
                    }
                } else {
                    $response['message'] = 'No user found with that email.';
                }
                $stmt->close();
            } else {
                $response['message'] = 'Email and password are required.';
            }
            break;
    }
}

$conn->close();
echo json_encode($response);
?>
