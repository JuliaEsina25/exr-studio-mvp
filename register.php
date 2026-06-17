<?php
session_start();
require_once __DIR__ . "/config/database.php";

$error = '';
$success = '';
$cartCount = 0;

if (isset($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    // Валидация
    if (empty($username)) {
        $error = 'Введите имя пользователя';
    } elseif (empty($email)) {
        $error = 'Введите email';
    } elseif (empty($password)) {
        $error = 'Введите пароль';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } else {
        // Проверяем, существует ли пользователь
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Пользователь с таким именем или email уже существует';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
            
            if ($stmt->execute()) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
                // Очищаем форму
                $_POST = array();
            } else {
                $error = 'Ошибка регистрации: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация | EXR Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        .register-container {
            max-width: 450px;
            margin: 150px auto 50px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .register-container h1 {
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
        .btn-register {
            width: 100%;
            padding: 14px;
            background: #d4b896;
            color: #1a1a2e;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-register:hover {
            background: #c2a575;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                <div class="cart-link"><a href="cart.php">🛒 Корзина <span class="cart-count"><?php echo $cartCount; ?></span></a></div>
            </div>
        </div>
    </header>

    <div class="register-container">
        <h1>Регистрация</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Имя пользователя *</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Полное имя</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Пароль * (минимум 6 символов)</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-register">Зарегистрироваться</button>
        </form>
        
        <div class="login-link">
            <a href="login.php">Уже есть аккаунт? Войти</a>
        </div>
    </div>
</body>
</html>
