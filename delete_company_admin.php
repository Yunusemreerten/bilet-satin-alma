<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['admin_id'])) {
    header('Location: manage_company_admins.php');
    exit;
}

$admin_id_to_delete = $_GET['admin_id'];

try {
            $stmt = $db->prepare("UPDATE User SET role = 'user', company_id = NULL WHERE id = ? AND role = 'company_admin'");
    $stmt->execute([$admin_id_to_delete]);

    header('Location: manage_company_admins.php?success=admin_deleted');
    exit;

} catch (Exception $e) {
    header('Location: manage_company_admins.php?error=delete_failed');
    exit;
}
?>