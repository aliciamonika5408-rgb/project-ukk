<?php
require_once '../config/database.php';
requireLogin();
session_destroy();
header('Location: ../auth/login.php');
exit();
?>
