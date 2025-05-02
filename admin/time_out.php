<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sitinId = isset($_POST['sitin_id']) ? (int)$_POST['sitin_id'] : 0;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First get the student's IDNO from the curr_sitin record
        $getIdnoStmt = $conn->prepare("SELECT IDNO FROM curr_sitin WHERE SITIN_ID = ?");
        $getIdnoStmt->bind_param("i", $sitinId);
        $getIdnoStmt->execute();
        $result = $getIdnoStmt->get_result();
        $row = $result->fetch_assoc();
        $idno = $row['IDNO'];
        $getIdnoStmt->close();
        
        // Update curr_sitin record
        $updateSitinStmt = $conn->prepare("UPDATE curr_sitin SET TIME_OUT = NOW(), STATUS = 'Completed' WHERE SITIN_ID = ?");
        $updateSitinStmt->bind_param("i", $sitinId);
        $updateSitinStmt->execute();
        $updateSitinStmt->close();
        
        // Decrease session count by 1
        $updateSessionStmt = $conn->prepare("UPDATE users SET SESSION = SESSION - 1 WHERE IDNO = ?");
        $updateSessionStmt->bind_param("s", $idno);
        $updateSessionStmt->execute();
        $updateSessionStmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update record: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
