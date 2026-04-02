<?php
require '../config/config.php';
checkAuth();

$student_id = $_SESSION['user']['id'];
$week = $_POST['week'];

$file = $_FILES['file']['name'];
$tmp = $_FILES['file']['tmp_name'];

$path = "uploads/" . time() . "_" . basename($file);
move_uploaded_file($tmp, "../" . $path);

$stmt = $conn->prepare("INSERT INTO reports (student_id, week, file_path, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iis", $student_id, $week, $path);
$stmt->execute();

header("Location: dashboard.php");
?>