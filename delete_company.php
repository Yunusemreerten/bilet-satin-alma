<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['company_id'])) {
    header('Location: manage_companies.php');
    exit;
}

$company_id_to_delete = $_GET['company_id'];

try {
                
    $stmt = $db->prepare("DELETE FROM Bus_Company WHERE id = ?");
    $stmt->execute([$company_id_to_delete]);

    header('Location: manage_companies.php?success=company_deleted');
    exit;

} catch (Exception $e) {
    header('Location: manage_companies.php?error=delete_failed');
    exit;
}
?>