<?php
session_start();
require '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Check if ID is provided and confirmed flag is set
if (isset($_GET['id']) && isset($_GET['confirmed']) && $_GET['confirmed'] === 'true') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Delete the student
    $query = "DELETE FROM users WHERE STUD_NUM = '$id'";
    
    if (mysqli_query($conn, $query)) {
        // Success - redirect with success flag
        header("Location: admin_studlist.php?deleted=success");
        exit;
    } else {
        // Error - redirect with error flag
        header("Location: admin_studlist.php?deleted=error");
        exit;
    }
} elseif (isset($_GET['id'])) {
    // If just ID is provided without confirmation, redirect back
    header("Location: admin_studlist.php");
    exit;
} else {
    // If no ID provided, redirect back
    header("Location: admin_studlist.php");
    exit;
}
?>