<?php
session_start();
require '../db.php';

if (isset($_POST['time_out']) && isset($_POST['sitin_id'])) {
    $sitinId = $_POST['sitin_id'];
    
    // First get the student's ID before updating the status
    $getIdStmt = $conn->prepare("SELECT IDNO FROM curr_sitin WHERE SITIN_ID = ?");
    $getIdStmt->bind_param("i", $sitinId);
    $getIdStmt->execute();
    $result = $getIdStmt->get_result();
    $student = $result->fetch_assoc();
    
    if ($student) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update the sit-in status to 'Completed'
            $updateStmt = $conn->prepare("UPDATE curr_sitin SET STATUS = 'Completed', TIME_OUT = NOW() WHERE SITIN_ID = ?");
            $updateStmt->bind_param("i", $sitinId);
            $updateStmt->execute();
            
            // Decrease the session count in users table
            $decreaseStmt = $conn->prepare("UPDATE users SET SESSION = SESSION - 1 WHERE IDNO = ? AND SESSION > 0");
            $decreaseStmt->bind_param("i", $student['IDNO']);
            $decreaseStmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Time-out successful and session deducted.";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = "Error processing time-out.";
        }
    }
    
    header("Location: admin_sitin.php");
    exit();
}
?>
