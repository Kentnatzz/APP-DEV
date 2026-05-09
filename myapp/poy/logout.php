<?php
require_once 'functions.php';

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'Logout', 'User logged out');
}

session_unset();
session_destroy();
redirect('login.php');
?>
