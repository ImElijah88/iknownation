<?php
/*
 * Logout Script
 * -------------
 * This script handles the user logout process.
 */

// The session manager is required to access session functions.
require_once 'components/session_manager.php';

// Call the logout function to clear all session data.
logoutUser();

// Redirect the user back to the main application page.
// Using an absolute path is more reliable.
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '\/');
header("Location: http://$host$uri/nownation.php");
exit;

?>