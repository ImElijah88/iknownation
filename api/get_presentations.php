<?php
/*
 * API: Get Presentations
 * ----------------------
 * This script fetches presentation data from the database and returns it as JSON.
 * It handles requests for both guest users and logged-in users.
 */

// Set the content type to JSON for all responses.
header('Content-Type: application/json');

// Include the database configuration and session manager.
require_once '../config/db_config.php';
require_once '../components/session_manager.php';

define('CACHE_DIR', __DIR__ . '/../cache');
define('CACHE_EXPIRATION', 3600); // 1 hour in seconds

if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$userId = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
}

$cacheKey = 'presentations_' . ($userId ? 'user_' . $userId : 'guest') . '_page_' . $page . '_limit_' . $limit . '.json';
$cacheFile = CACHE_DIR . '/' . $cacheKey;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_EXPIRATION) {
    echo file_get_contents($cacheFile);
    exit;
}

try {
    $presentations = [];
    $totalPresentations = 0;

    // Get total number of presentations
    $countSql = "SELECT COUNT(*) as total FROM presentations WHERE user_id IS NULL";
    if ($userId) {
        $countSql .= " OR user_id = ?";
    }
    $countStmt = $conn->prepare($countSql);
    if ($userId) {
        $countStmt->bind_param("i", $userId);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalPresentations = $countResult->fetch_assoc()['total'];
    $countStmt->close();

    // Prepare the SQL statement.
    // If a user is logged in, fetch their presentations AND the default ones (user_id IS NULL).
    // If they are a guest, fetch only the default ones.
    $sql = "SELECT p.id, p.user_id, p.presentation_key, p.title, p.slides, p.quizzes, p.speech, p.colors, p.status, t.template_name FROM presentations p JOIN templates t ON p.template_id = t.id WHERE p.user_id IS NULL";

    if ($userId) {
        $sql .= " OR p.user_id = ?";
    }

    $sql .= " LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    if ($userId) {
        $stmt->bind_param("iii", $userId, $limit, $offset);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }

    // Execute the query and process the results.
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $response = [
                'total_presentations' => 0,
                'presentations' => []
            ];
        } else {
            while ($row = $result->fetch_assoc()) {
                // Decode JSON fields into arrays/objects
                $row['slides'] = json_decode($row['slides'], true);
                $row['quizzes'] = json_decode($row['quizzes'], true);
                $row['colors'] = json_decode($row['colors'], true);

                // Use presentation_key as the key in the final JSON output
                $presentations[$row['presentation_key']] = $row;
            }
            $response = [
                'total_presentations' => $totalPresentations,
                'presentations' => $presentations
            ];
        }
    } else {
        throw new Exception("Failed to execute query.");
    }

    $stmt->close();
    $conn->close();

    $jsonResponse = json_encode($response);
    file_put_contents($cacheFile, $jsonResponse);
    echo $jsonResponse;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred.']);
}

?>
