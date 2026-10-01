<?php
session_start();
header('Location: ' . (empty($_SESSION['user']) ? 'login.php' : 'dashboard.php'));
