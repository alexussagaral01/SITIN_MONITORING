<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement_id']) && isset($_POST['content'])) {
    $id = $_POST['announcement_id'];
    $content = $_POST['content'];
    
    $stmt = $conn->prepare("UPDATE announcement SET CONTENT = ? WHERE ID = ?");
    $stmt->bind_param("si", $content, $id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
    exit();
}

echo "invalid request";
