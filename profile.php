<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM proposals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$proposals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Tarkov Community</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .proposal-item {
            background: #1a1a1a;
            border-left: 4px solid #ff6b00;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-approved { background: #28a745; color: #fff; }
        .status-rejected { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>ЛИЧНЫЙ КАБИНЕТ</h1>
            <a href="logout.php" class="btn btn-secondary">Выйти</a>
        </div>
        
        <div class="profile-info" style="background: #1a1a1a; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <p><strong>Логин:</strong> <?= htmlspecialchars($user['login']) ?></p>
            <p><strong>ФИО:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone']) ?></p>
            <p><strong>Дата рождения:</strong> <?= $user['birth_date'] ?></p>
            <p><strong>О себе:</strong> <?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        </div>
        
        <h2>Мои предложения оружия (<?= count($proposals) ?>)</h2>
        
        <?php if (empty($proposals)): ?>
            <p>У вас пока нет предложений. <a href="index.html#proposals">Предложить оружие</a></p>
        <?php else: ?>
            <?php foreach ($proposals as $p): ?>
                <div class="proposal-item">
                    <h3><?= htmlspecialchars($p['weapon_name']) ?></h3>
                    <p><strong>Тип:</strong> <?= htmlspecialchars($p['weapon_type']) ?></p>
                    <p><strong>Калибр:</strong> <?= htmlspecialchars($p['weapon_caliber']) ?></p>
                    <p><strong>Страна:</strong> <?= htmlspecialchars($p['weapon_country'] ?: 'Не указана') ?></p>
                    <p><strong>Статус:</strong> 
                        <span class="status status-<?= $p['status'] ?>">
                            <?= $p['status'] == 'pending' ? 'На модерации' : 
                                ($p['status'] == 'approved' ? 'Одобрено' : 'Отклонено') ?>
                        </span>
                    </p>
                    <details>
                        <summary>Подробнее</summary>
                        <p><strong>Описание:</strong> <?= nl2br(htmlspecialchars($p['weapon_description'])) ?></p>
                        <p><strong>Почему нужно в Таркове:</strong> <?= nl2br(htmlspecialchars($p['weapon_reason'])) ?></p>
                    </details>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="index.php">← На главную</a>
        </p>
    </div>
</body>
</html>