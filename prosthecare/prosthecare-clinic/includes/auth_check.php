<?php
/**
 * ProstheCare Clinic Management System
 * Authentication Check Helper
 */

require_once __DIR__ . '/../config/supabase.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$currentUser = $_SESSION['user'];
