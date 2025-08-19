<?php
require_once 'includes/functions.php';
startSessionIfNotStarted();

// Clear all session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>