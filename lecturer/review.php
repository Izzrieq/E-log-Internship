<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../config/config.php';
checkAuth();
if(!isLecturer()) die(); // only lecturer/admin

// Handle review submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {

    $report_id = intval($_POST['id']); // sanitize
    $status = $_POST['status'] === 'approved' ? 'approved' : 'rejected';
    $comment = isset($_POST['comment']) ? $conn->real_escape_string($_POST['comment']) : '';

    // Optional: check if report exists
    $res = $conn->query("SELECT id FROM reports WHERE id = $report_id");
    if($res->num_rows == 0){
        die("Report not found.");
    }

    // Update status & comment
    $conn->query("
        UPDATE reports 
        SET status='$status', comment='$comment'
        WHERE id=$report_id
    ");

    // Redirect back to avoid resubmission
    header("Location: dashboard.php");
    exit;
}
?>