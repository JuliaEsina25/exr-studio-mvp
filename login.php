<?php
session_start();
require_once __DIR__ . "/config/database.php";

$error = '';
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // ============================================================
    // ПРОВЕРКА ПАРОЛЯ (работает с хешем и открытым текстом)
    // ============================================================
    $valid = false;
    
    if ($user) {
        // 1. Проверка через password_verify (если пароль захеширован)
        if (password_verify($password, $user['password'])) {
            $valid = true;
        }
        
        // 2. Если пароль в БД в открытом виде — сравниваем напрямую
        if ($password === $user['password']) {
            $valid = true;
            
            // АВТОМАТИЧЕСКИ ХЕШИРУЕМ ПАРОЛЬ В БД
            try {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->bind_param("si", $hashed, $user['id']);
                $update->execute();
            } catch (Exception $e) {
                // Игнорируем ошибку обновления
            }
        }
    }
    
    if ($valid) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
        $_SESSION['user_email'] = $user['email'];
        header('Location: account.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход | EXR Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        .login-container {
            max-width: 450px;
            margin: 150px auto 50px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #1a1a2e;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a3728;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e8dcc8;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #d4b896;
            box-shadow: 0 0 0 3px rgba(212, 184, 150, 0.2);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #d4b896;
            color: #1a1a2e;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #c2a575;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #d4b896;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .demo-credentials {
            background: #f5f0eb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #4a3728;
        }
        .demo-credentials strong {
            color: #1a1a2e;
        }
        .demo-credentials .demo-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="location.href='index.php'">
                    <div class="logo-mixed">
                        <span class="logo-letter logo-e">E</span>
                        <span class="logo-letter logo-r">R</span>
                    </div>
                    <span class="logo-subtitle">Мастерская одежды</span>
                </div>
                <div class="cart-link"><a href="cart.php">🛒 Корзина <span class="cart-count"><?= $cartCount ?></span></a></div>
            </div>
        </div>
    </header>

    <div class="login-container">
        <h1>Вход в личный кабинет</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Тестовые данные -->
        <div class="demo-credentials">
            <strong>Тестовые данные:</strong>
            <div class="demo-row">
                <span>Логин:</span>
                <span><strong>admin@exr-studio.ru</strong></span>
            </div>
            <div class="demo-row">
                <span>Пароль:</span>
                <span><strong>123456</strong></span>
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="example@mail.ru" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="Введите пароль" required>
            </div>
            <button type="submit" class="btn-login">Войти</button>
        </form>
        
        <div class="register-link">
            <a href="register.php">Нет аккаунта? Зарегистрироваться</a>
        </div>
    </div>
</body>
</html>