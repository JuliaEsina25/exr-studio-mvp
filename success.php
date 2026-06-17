<?php
session_start();
$order_number = $_GET['order'] ?? '';
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ оформлен | EXR Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 150px auto 50px;
            padding: 50px;
            background: white;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 80px;
            color: #27ae60;
            margin-bottom: 20px;
        }
        .success-container h1 {
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .order-number {
            background: #f5f0e8;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 18px;
        }
        .btn-continue {
            display: inline-block;
            padding: 12px 30px;
            background: #d4b896;
            color: #1a1a2e;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
            margin-top: 20px;
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

    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Спасибо за заказ!</h1>
        <p>Ваш заказ успешно оформлен.</p>
        
        <?php if ($order_number): ?>
            <div class="order-number">
                Номер заказа: <strong><?= htmlspecialchars($order_number) ?></strong>
            </div>
        <?php endif; ?>
        
        <p>В ближайшее время наш менеджер свяжется с вами для уточнения деталей.</p>
        <a href="index.php" class="btn-continue">Продолжить покупки</a>
    </div>
</body>
</html>
