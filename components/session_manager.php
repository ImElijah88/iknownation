<?php
/*
 * Session Manager
 *
 * This component manages user sessions, including starting sessions,
 * checking login status, and verifying user roles.
 */

// Start a session on every page that includes this manager.
// This must be called before any HTML is output.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks for a "Remember Me" cookie and logs the user in if valid.
 * This should be called on pages that require user authentication.
 */
function checkAndLoginFromCookie($conn) {
    // Only proceed if the user is not already logged in via session
    if (!isLoggedIn() && isset($_COOKIE['remember_me_token'])) {
        
        list($user_id, $token) = explode(':', $_COOKIE['remember_me_token'], 2);

        if (empty($user_id) || empty($token)) {
            return; // Invalid cookie format
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify the token and its expiry
            if ($user['remember_token'] && $user['remember_token_expiry'] && hash_equals($user['remember_token'], hash('sha256', $token))) {
                
                if (new DateTime() < new DateTime($user['remember_token_expiry'])) {
                    // Token is valid and not expired, log the user in
                    loginUser($user);

                    // --- Token Rotation for Security ---
                    // Generate a new token, update the database, and reset the cookie
                    $new_token = bin2hex(random_bytes(32));
                    $new_hashed_token = hash('sha256', $new_token);
                    $new_expiry_date = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

                    $update_stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_token_expiry = ? WHERE id = ?");
                    $update_stmt->bind_param("ssi", $new_hashed_token, $new_expiry_date, $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    $cookie_name = 'remember_me_token';
                    $cookie_value = $user['id'] . ':' . $new_token;
                    $cookie_expiry = time() + (86400 * 30);
                    setcookie($cookie_name, $cookie_value, ['expires' => $cookie_expiry, 'path' => '/', 'samesite' => 'Strict', 'secure' => true, 'httponly' => true]);

                } else {
                    // Token has expired, clear it from the database and the cookie
                    $update_stmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_token_expiry = NULL WHERE id = ?");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    setcookie('remember_me_token', '', time() - 3600, "/");
                }
            }
        }
        $stmt->close();
        $conn->close();
    }
}

// Automatically check for the remember me cookie on script load
checkAndLoginFromCookie($conn);


/**
 * Checks if a user is currently logged in.
 *
 * @return bool True if the user is logged in, false otherwise.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

/**
 * @deprecated Use hasRole('admin') instead.
 * Checks if the logged-in user is an administrator.
 *
 * @return bool True if the user is an admin, false otherwise.
 */
function isAdmin(): bool
{
    return hasRole('admin');
}

/**
 * Checks if the logged-in user has a specific role.
 *
 * @param string $role The role to check for.
 * @return bool True if the user has the specified role, false otherwise.
 */
function hasRole(string $role): bool
{
    return isLoggedIn() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
}

/**
 * Gets the currently logged-in user's data.
 *
 * @return array|null The user's data as an array, or null if not logged in.
 */
function getCurrentUser(): ?array
{
    if (isLoggedIn()) {
        return $_SESSION['user'];
    }
    return null;
}

/**
 * Logs a user in by setting their session data.
 *
 * @param array $user The user data to store in the session.
 */
function loginUser(array $user): void
{
    // Unset sensitive data before storing in session
    unset($user['password']);
    
    // Decode progress data if it's a JSON string
    if (!empty($user['progress_data']) && is_string($user['progress_data'])) {
        $user['progress_data'] = json_decode($user['progress_data'], true);
    } else {
        $user['progress_data'] = [];
    }

    $_SESSION['user'] = $user;
    session_regenerate_id(true);
}

/**
 * Logs the current user out by destroying the session.
 */
function logoutUser(): void
{
    $_SESSION = array(); // Unset all session variables

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Also destroy the remember me cookie
    if (isset($_COOKIE['remember_me_token'])) {
        setcookie('remember_me_token', '', ['expires' => time() - 3600, 'path' => '/', 'samesite' => 'Strict', 'secure' => true, 'httponly' => true]);
    }

    session_destroy();
}

?>
