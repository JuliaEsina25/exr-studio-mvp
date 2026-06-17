<?php
session_start();
require_once __DIR__ . "/config/database.php";

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_address = $_POST['customer_address'] ?? '';
    
    // Сохраняем товары в JSON
    $items_json = json_encode(array_values($cart), JSON_UNESCAPED_UNICODE);
    
    $order_number = 'ORD-' . date('Ymd') . '-' . rand(100, 999);
    
    $sql = "INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, customer_address, total, items, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $order_number, $customer_name, $customer_phone, $customer_email, $customer_address, $total, $items_json);
    
    if ($stmt->execute()) {
        $_SESSION['cart'] = [];
        header('Location: success.php?order=' . $order_number);
        exit;
    } else {
        $error = "Ошибка оформления заказа: " . $conn->error;
    }
}

$cartCount = array_sum(array_column($cart, 'quantity'));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа | EXR Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
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
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e8dcc8;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #d4b896;
            color: #1a1a2e;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover {
            background: #c2a575;
        }
        .order-summary {
            background: #f5f0e8;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .order-summary h3 {
            margin-bottom: 15px;
            color: #1a1a2e;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e8dcc8;
        }
        .total {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #d4b896;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #d4b896;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .section-title {
            text-align: center;
            margin-bottom: 30px;
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

    <div class="checkout-container">
        <h1 class="section-title">Оформление заказа</h1>
        
        <div class="order-summary">
            <h3>Ваш заказ</h3>
            <?php foreach ($cart as $item): ?>
                <div class="order-item">
                    <span><?= htmlspecialchars($item['name']) ?> (<?= $item['size'] ?>) x<?= $item['quantity'] ?></span>
                    <span><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?> ₽</span>
                </div>
            <?php endforeach; ?>
            <div class="total">
                Итого: <?= number_format($total, 0, '', ' ') ?> ₽
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="checkout-form">
            <div class="form-group">
                <label>Ваше имя *</label>
                <input type="text" name="customer_name" required>
            </div>
            <div class="form-group">
                <label>Телефон *</label>
                <input type="tel" name="customer_phone" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="customer_email">
            </div>
            <div class="form-group">
                <label>Адрес доставки *</label>
                <textarea name="customer_address" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn-submit">Подтвердить заказ</button>
        </form>
    </div>
</body>
</html>
