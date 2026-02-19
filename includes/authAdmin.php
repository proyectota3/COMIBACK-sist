<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['comiback_logged_in']) || $_SESSION['comiback_logged_in'] !== true) {
    header("Location: /COMIBACK/index.php");
    exit();
}
?>