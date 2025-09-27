<?php
/*
 * API: Export Presentation
 * --------------------------
 * This script exports a presentation in various formats (HTML, TXT).
 * It requires a `presentation_id` and `format` as GET parameters.
 */

// Include the database configuration.
require_once '../config/db_config.php';

// --- Input Validation ---
if (!isset($_GET['presentation_id']) || !isset($_GET['format'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request: Missing presentation_id or format.']);
    exit;
}

$presentationId = (int)$_GET['presentation_id'];
$format = strtolower($_GET['format']);
$allowedFormats = ['html', 'txt'];

if (!in_array($format, $allowedFormats)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request: Invalid format specified.']);
    exit;
}

// --- Database Query ---
$sql = "SELECT title, slides, quizzes FROM presentations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $presentationId);

$presentation = null;
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $presentation = $row;
        $presentation['slides'] = json_decode($row['slides'], true);
        $presentation['quizzes'] = json_decode($row['quizzes'], true);
    }
}

$stmt->close();
$conn->close();

if (!$presentation) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found: Presentation not found.']);
    exit;
}

// --- File Generation ---
$title = preg_replace('/[^a-zA-Z0-9_]/', '_', $presentation['title']); // Sanitize title for filename
$content = '';
$filename = $title . '.' . $format;

switch ($format) {
    case 'html':
        $content .= '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>' . htmlspecialchars($presentation['title']) . '</title></head><body>';
        $content .= '<h1>' . htmlspecialchars($presentation['title']) . '</h1>';
        foreach ($presentation['slides'] as $slide) {
            $content .= '<h2>' . htmlspecialchars($slide['title']) . '</h2>';
            $content .= '<div>' . $slide['content'] . '</div><hr>'; // Assuming slide content is already HTML
        }
        // Add quizzes to the export
        if (!empty($presentation['quizzes'])) {
            $content .= '<h1>Quizzes</h1>';
            foreach ($presentation['quizzes'] as $quiz) {
                $content .= '<h2>' . htmlspecialchars($quiz['question']) . '</h2><ul>';
                foreach ($quiz['options'] as $option) {
                    $content .= '<li>' . htmlspecialchars($option) . '</li>';
                }
                $content .= '</ul><p><b>Answer:</b> ' . htmlspecialchars($quiz['answer']) . '</p><hr>';
            }
        }
        $content .= '</body></html>';
        header('Content-Type: text/html');
        break;

    case 'txt':
        $content .= $presentation['title'] . "\r\n=====================================\r\n\r\n";
        foreach ($presentation['slides'] as $slide) {
            $content .= $slide['title'] . "\r\n-------------------------------------";
            $content .= strip_tags($slide['content']) . "\r\n\r\n";
        }
        if (!empty($presentation['quizzes'])) {
            $content .= "\r\n\r\nQuizzes\r\n=====================================\r\n\r\n";
            foreach ($presentation['quizzes'] as $quiz) {
                $content .= 'Q: ' . $quiz['question'] . "\r\n";
                foreach ($quiz['options'] as $index => $option) {
                    $content .= ($index + 1) . '. ' . $option . "\r\n";
                }
                $content .= 'Answer: ' . $quiz['answer'] . "\r\n\r\n";
            }
        }
        header('Content-Type: text/plain');
        break;
}

// --- Headers for Download ---
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

echo $content;
exit;
?>