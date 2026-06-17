<?php
// ============================================================
// ХЕДЕР САЙТА
// ============================================================

// Если сессия ещё не стартовала
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Количество товаров в корзине
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EXR Studio — Мастерская одежды</title>
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="location.href='/exr-studio-mvp/index.php'">
                    <div class="logo-mixed">
                        <span class="logo-letter logo-e">E</span>
                        <span class="logo-letter logo-r">R</span>
                    </div>
                    <span class="logo-subtitle">Мастерская одежды</span>
                </div>
                <nav>
                    <ul>
                        <li><a href="/exr-studio-mvp/index.php">Главная</a></li>
                        <li><a href="/exr-studio-mvp/index.php#collections">Коллекции</a></li>
                        <li><a href="/exr-studio-mvp/index.php#about">О нас</a></li>
                        <li><a href="/exr-studio-mvp/index.php#contacts">Контакты</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <a href="/exr-studio-mvp/cart.php" class="cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?= $cartCount ?></span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/exr-studio-mvp/account.php" class="user-link">
                            <i class="fas fa-user-circle"></i>
                        </a>
                        <a href="/exr-studio-mvp/logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="/exr-studio-mvp/login.php" class="login-btn">Войти</a>
                        <a href="/exr-studio-mvp/register.php" class="register-btn">Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>