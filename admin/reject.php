<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("UPDATE proposals SET status = 'rejected' WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit();
?>