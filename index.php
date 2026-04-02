<?php
require 'config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
} else {
    if (isStudent()) {
        header("Location: student/dashboard.php");
    } else {
        header("Location: lecturer/dashboard.php");
    }
}
?>