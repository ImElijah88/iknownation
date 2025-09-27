<?php
/*
 * API: Generate Presentation
 * ---------------------------
 * This script handles file uploads and orchestrates the generation of presentation content
 * using the Neo agent, which interacts with an LLM and (in the future) an MPV server.
 */

header('Content-Type: application/json');
require_once '../config/db_config.php';
require_once '../components/session_manager.php';
require_once '../classes/Neo.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// --- Security Check ---
if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in to generate a presentation.';
    echo json_encode($response);
    exit;
}

// --- File Upload Handling ---
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['document'];
    $userId = getCurrentUser()['id'];
    $templateId = isset($_POST['template']) ? (int)$_POST['template'] : 1;

    // Retrieve template features from the database
    $templateFeatures = [];
    $stmtTemplate = $conn->prepare("SELECT features FROM templates WHERE id = ?");
    $stmtTemplate->bind_param("i", $templateId);
    $stmtTemplate->execute();
    $resultTemplate = $stmtTemplate->get_result();
    if ($resultTemplate->num_rows > 0) {
        $rowTemplate = $resultTemplate->fetch_assoc();
        $templateFeatures = json_decode($rowTemplate['features'], true);
    }
    $stmtTemplate->close();

    $title = pathinfo($file['name'], PATHINFO_FILENAME);
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    try {
        $agent = new Neo($conn);

        // Extract file content using the agent (handles .txt directly, others via placeholder)
        $fileContent = $agent->extractFileContent($file['tmp_name'], $fileExtension);

        // Get dynamic form inputs
        $dynamicInputs = [];
        foreach ($_POST as $key => $value) {
            if ($key !== 'document' && $key !== 'template') {
                $dynamicInputs[$key] = $value;
            }
        }

        // Generate presentation content using the agent
        $generatedContent = $agent->generatePresentationContent($fileContent, $templateFeatures, $dynamicInputs);

        // Extract data from generated content
        $slides = json_encode($generatedContent['slides']);
        $quizzes = json_encode($generatedContent['quizzes']);
        $speech = $generatedContent['speech'];
        $colors = json_encode($generatedContent['colors']);
        $title = $generatedContent['title'];

        // Use the title from LLM if available, otherwise fallback to filename
        if (empty($title)) {
            $title = pathinfo($file['name'], PATHINFO_FILENAME);
        }

        $presentation_key = strtolower(preg_replace('/[^a-zA-Z0-9-]/ ', '-', $title)) . '-' . uniqid();

        // --- Database Insertion ---
        // A newly generated presentation is always created with status = 'temporary'
        $stmt = $conn->prepare("INSERT INTO presentations (user_id, template_id, presentation_key, title, slides, quizzes, speech, colors, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'temporary')");
        $stmt->bind_param("iissssss", $userId, $templateId, $presentation_key, $title, $slides, $quizzes, $speech, $colors);

        if ($stmt->execute()) {
            $newPresentationId = $stmt->insert_id;
            $response = [
                'status' => 'success',
                'message' => 'Presentation generated successfully!',
                'presentation_id' => $newPresentationId,
                'presentation_key' => $presentation_key
            ];
        } else {
            $response['message'] = 'Failed to save the presentation to the database.';
        }

        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = 'Generation Error: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'No document was uploaded.';
}

$conn->close();
echo json_encode($response);
?>