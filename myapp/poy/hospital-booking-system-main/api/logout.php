<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

if (isLoggedIn()) {
    logoutUser($link);
}

header('Location: ../login.php');
exit;
?>
