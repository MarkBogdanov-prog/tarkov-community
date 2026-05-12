<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$weapon_name = trim($_POST['weapon_name'] ?? '');
$weapon_type = $_POST['weapon_type'] ?? '';
$weapon_caliber = trim($_POST['weapon_caliber'] ?? '');
$weapon_country = trim($_POST['weapon_country'] ?? '');
$weapon_description = trim($_POST['weapon_description'] ?? '');
$weapon_reason = trim($_POST['weapon_reason'] ?? '');

$errors = [];

if (empty($weapon_name)) $errors[] = 'Название оружия обязательно';
if (empty($weapon_type)) $errors[] = 'Тип оружия обязателен';
if (empty($weapon_caliber)) $errors[] = 'Калибр обязателен';
if (empty($weapon_description)) $errors[] = 'Описание обязательно';
if (empty($weapon_reason)) $errors[] = 'Причина добавления обязательна';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO proposals (user_id, weapon_name, weapon_type, weapon_caliber, weapon_country, weapon_description, weapon_reason)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $weapon_name, $weapon_type, $weapon_caliber, $weapon_country, $weapon_description, $weapon_reason]);
    
    echo json_encode(['success' => true, 'message' => 'Предложение отправлено на модерацию']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
?>