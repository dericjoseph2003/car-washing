<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} else {
    if ($_SESSION['role'] == "admin") {
        header("Location: index.php");
    } else {
        header("Location: admindash.php");
    }
    exit();
}
?>
