<?php
include "config.php";

// Destroy session
session_destroy();

// Show logout message and redirect
showAlert('success', 'You have been logged out successfully');

// Redirect to home
redirect("index.php");
?>
