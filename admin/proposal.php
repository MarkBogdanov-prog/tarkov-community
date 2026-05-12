<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT p.*, u.login, u.full_name, u.email, u.phone 
    FROM proposals p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$proposal = $stmt->fetch();

if (!$proposal) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр предложения</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <h1>Просмотр предложения #<?= $proposal['id'] ?></h1>
        
        <div style="background: #1a1a1a; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Информация о пользователе</h3>
            <p><strong>Логин:</strong> <?= htmlspecialchars($proposal['login']) ?></p>
            <p><strong>ФИО:</strong> <?= htmlspecialchars($proposal['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($proposal['email']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($proposal['phone']) ?></p>
            
            <hr>
            
            <h3>Информация об оружии</h3>
            <p><strong>Название:</strong> <?= htmlspecialchars($proposal['weapon_name']) ?></p>
            <p><strong>Тип:</strong> <?= htmlspecialchars($proposal['weapon_type']) ?></p>
            <p><strong>Калибр:</strong> <?= htmlspecialchars($proposal['weapon_caliber']) ?></p>
            <p><strong>Страна:</strong> <?= htmlspecialchars($proposal['weapon_country'] ?: 'Не указана') ?></p>
            
            <h3>Описание</h3>
            <p><?= nl2br(htmlspecialchars($proposal['weapon_description'])) ?></p>
            
            <h3>Почему нужно в Таркове?</h3>
            <p><?= nl2br(htmlspecialchars($proposal['weapon_reason'])) ?></p>
            
            <p><strong>Статус:</strong> 
                <span class="status status-<?= $proposal['status'] ?>">
                    <?= $proposal['status'] == 'pending' ? 'На модерации' : 
                        ($proposal['status'] == 'approved' ? 'Одобрено' : 'Отклонено') ?>
                </span>
            </p>
            <p><strong>Дата подачи:</strong> <?= $proposal['created_at'] ?></p>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <a href="approve.php?id=<?= $proposal['id'] ?>" class="btn btn-primary">Одобрить</a>
            <a href="reject.php?id=<?= $proposal['id'] ?>" class="btn btn-secondary" style="background:#dc3545;">Отклонить</a>
            <a href="index.php" class="btn btn-secondary">Назад</a>
        </div>
    </div>
</body>
</html>