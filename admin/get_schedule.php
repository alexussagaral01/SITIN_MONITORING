<?php
session_start();
require '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required']);
    exit;
}

$schedule_id = intval($_GET['id']);

// Fetch the schedule from database
$query = "SELECT * FROM lab_schedule WHERE SCHED_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Schedule not found']);
    exit;
}

// Get schedule data
$schedule = $result->fetch_assoc();

// Return as JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'schedule' => $schedule]);

$stmt->close();
$conn->close();
?>
