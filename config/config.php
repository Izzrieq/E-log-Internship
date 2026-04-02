<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "internship_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection Failed");
}

function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header("Location: /internship/auth/login.php");
        exit();
    }
}

function isStudent() {
    return $_SESSION['user']['role'] === 'student';
}

function isLecturer() {
    return $_SESSION['user']['role'] === 'lecturer';
}
?>