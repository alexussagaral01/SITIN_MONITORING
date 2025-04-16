<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => ''];
    
    if (isset($_POST['studentId'])) {
        $studentId = (int)$_POST['studentId'];
        
        // Get current points and sessions
        $query = "SELECT POINTS, SESSION FROM users WHERE STUD_NUM = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            $newPoints = $user['POINTS'] + 1;
            $newSession = $user['SESSION'];
            
            // Check if points reached 3
            if ($newPoints >= 3) {
                $newPoints = 0; // Reset points
                $newSession = $user['SESSION'] + 1; // Add 1 session
            }
            
            // Update points and session
            $updateQuery = "UPDATE users SET POINTS = ?, SESSION = ? WHERE STUD_NUM = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("iii", $newPoints, $newSession, $studentId);
            
            if ($updateStmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Points updated successfully';
                $response['newPoints'] = $newPoints;
                $response['newSession'] = $newSession;
            } else {
                $response['message'] = 'Failed to update points';
            }
            
            $updateStmt->close();
        } else {
            $response['message'] = 'Student not found';
        }
        
        $stmt->close();
    }
    
    echo json_encode($response);
    exit;
}
?>
