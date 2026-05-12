<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Статистика
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM proposals
")->fetch();

// Типы оружия
$weaponTypes = $pdo->query("
    SELECT weapon_type, COUNT(*) as count 
    FROM proposals 
    GROUP BY weapon_type 
    ORDER BY count DESC
")->fetchAll();

// Последние предложения
$proposals = $pdo->query("
    SELECT p.*, u.login, u.full_name 
    FROM proposals p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 20
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .admin-table th { background: #ff6b00; color: white; padding: 10px; }
        .admin-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; }
        .stat-card { background: #1a1a1a; padding: 20px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 2rem; color: #ff6b00; font-weight: bold; }
        .status { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 0.8rem; }
        .status-pending { background: #ffc107; color: #000; }
        .status-approved { background: #28a745; color: #fff; }
        .status-rejected { background: #dc3545; color: #fff; }
        .action-btn { padding: 5px 10px; margin: 0 2px; border: none; border-radius: 3px; cursor: pointer; display: inline-block; text-decoration: none; font-size: 0.8rem; }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-view { background: #ff6b00; color: white; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>АДМИН-ПАНЕЛЬ</h1>
            <a href="logout.php" class="btn btn-secondary">Выйти</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div>Всего предложений</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending'] ?></div>
                <div>На модерации</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['approved'] ?></div>
                <div>Одобрено</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['rejected'] ?></div>
                <div>Отклонено</div>
            </div>
        </div>
        
        <h2>Статистика по типам оружия</h2>
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
            <?php foreach ($weaponTypes as $type): ?>
                <div class="stat-card" style="flex: 1; min-width: 120px;">
                    <div class="stat-number"><?= $type['count'] ?></div>
                    <div><?= $type['weapon_type'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <h2>Последние предложения</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Оружие</th>
                    <th>Тип</th>
                    <th>Калибр</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proposals as $p): ?>
                 <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['login']) ?></td>
                    <td><?= htmlspecialchars($p['weapon_name']) ?></td>
                    <td><?= htmlspecialchars($p['weapon_type']) ?></td>
                    <td><?= htmlspecialchars($p['weapon_caliber']) ?></td>
                    <td>
                        <span class="status status-<?= $p['status'] ?>">
                            <?= $p['status'] == 'pending' ? 'На модерации' : 
                                ($p['status'] == 'approved' ? 'Одобрено' : 'Отклонено') ?>
                        </span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($p['created_at'])) ?></td>
                    <td class="actions">
                        <a href="proposal.php?id=<?= $p['id'] ?>" class="action-btn btn-view">👁️</a>
                        <a href="approve.php?id=<?= $p['id'] ?>" class="action-btn btn-approve">✅</a>
                        <a href="reject.php?id=<?= $p['id'] ?>" class="action-btn btn-reject">❌</a>
                    </td>
                 </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>