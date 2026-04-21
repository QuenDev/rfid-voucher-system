<?php
/**
 * Logout Handler
 * Properly handles logout from client directory
 */

require_once '../server/includes/auth.php';

// Call logout function which handles session destruction and redirect
logout();
?>
