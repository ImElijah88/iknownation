<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

$sql = "SELECT email, progress_data FROM users";
$result = $conn->query($sql);

$leaderboard = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $totalXp = 0;
        if ($row['progress_data']) {
            $progressData = json_decode($row['progress_data'], true);
            if (is_array($progressData)) {
                foreach ($progressData as $presentationProgress) {
                    if (isset($presentationProgress['xp'])) {
                        $totalXp += (int)$presentationProgress['xp'];
                    }
                }
            }
        }
        $leaderboard[] = [
            'email' => $row['email'],
            'total_xp' => $totalXp
        ];
    }
}

// Sort the leaderboard by total_xp in descending order
usort($leaderboard, function($a, $b) {
    return $b['total_xp'] - $a['total_xp'];
});

$conn->close();
echo json_encode($leaderboard);
?>