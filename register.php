<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $bio = trim($_POST['bio'] ?? '');
    $agreed = isset($_POST['agreed']) ? 1 : 0;
    
    // Валидация
    if (empty($login)) $errors[] = 'Логин обязателен';
    elseif (strlen($login) < 3) $errors[] = 'Логин должен быть не менее 3 символов';
    
    if (empty($password)) $errors[] = 'Пароль обязателен';
    elseif (strlen($password) < 6) $errors[] = 'Пароль должен быть не менее 6 символов';
    
    if ($password !== $password_confirm) $errors[] = 'Пароли не совпадают';
    
    if (empty($full_name)) $errors[] = 'ФИО обязательно';
    if (empty($phone)) $errors[] = 'Телефон обязателен';
    if (empty($email)) $errors[] = 'Email обязателен';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if (empty($birth_date)) $errors[] = 'Дата рождения обязательна';
    if (empty($gender)) $errors[] = 'Пол обязателен';
    if (empty($bio)) $errors[] = 'Биография обязательна';
    if (!$agreed) $errors[] = 'Необходимо согласие на обработку данных';
    
    // Проверка уникальности
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ? OR email = ?");
        $stmt->execute([$login, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким логином или email уже существует';
        }
    }
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (login, password_hash, full_name, phone, email, birth_date, gender, bio, agreed)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$login, $password_hash, $full_name, $phone, $email, $birth_date, $gender, $bio, $agreed]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_login'] = $login;
        
        header('Location: profile.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Tarkov Community</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <h1>РЕГИСТРАЦИЯ</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?= $error ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Логин *</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Пароль *</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Подтверждение пароля *</label>
                <input type="password" name="password_confirm" required>
            </div>
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Телефон *</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Дата рождения *</label>
                <input type="date" name="birth_date" required>
            </div>
            <div class="form-group">
                <label>Пол *</label>
                <select name="gender" required>
                    <option value="male">Мужской</option>
                    <option value="female">Женский</option>
                </select>
            </div>
            <div class="form-group">
                <label>О себе</label>
                <textarea name="bio" rows="4" required></textarea>
            </div>
            
            <!-- НОВЫЙ ЧЕКБОКС СОГЛАСИЯ -->
            <div class="form-group">
                <label>
                    <input type="checkbox" name="agreed" value="1" required>
                    Я согласен на обработку персональных данных *
                </label>
            </div>
            
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p style="text-align: center; margin-top: 20px;">
            <a href="login.php">Уже есть аккаунт? Войти</a>
        </p>
    </div>
</body>
</html>