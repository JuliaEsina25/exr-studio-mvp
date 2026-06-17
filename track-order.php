<?php
session_start();
$db = require_once __DIR__ . '/config/database.php';

$order = null;
$error = null;
$orderNumber = null;

// Если передан номер заказа в GET
if (isset($_GET['order_number'])) {
    $orderNumber = trim($_GET['order_number']);
    
    // Ищем заказ по номеру
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $error = "Заказ с номером $orderNumber не найден";
    }
}

// Если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($phone)) {
        $error = "Введите номер телефона";
    } else {
        // Ищем заказы по номеру телефона
        $stmt = $db->prepare("SELECT * FROM orders WHERE customer_phone LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$phone%"]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($orders)) {
            $error = "Заказы с номером телефона $phone не найдены";
        }
    }
}

// Подсчет товаров в корзине для хедера
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отследить заказ - EXR</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .track-section {
            padding: 120px 0 80px;
            min-height: 80vh;
        }
        .track-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        .track-icon {
            text-align: center;
            font-size: 64px;
            color: #ff6b6b;
            margin-bottom: 20px;
        }
        .track-title {
            text-align: center;
            font-size: 28px;
            margin-bottom: 30px;
        }
        .search-form {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-search {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-search:hover {
            background: #ff6b6b;
        }
        .order-card {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            background: #f9f9f9;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .order-number {
            font-size: 18px;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-processing { background: #fff3e0; color: #f57c00; }
        .status-shipped { background: #e8f5e9; color: #388e3c; }
        .status-delivered { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #d32f2f; }
        .order-info {
            margin: 10px 0;
        }
        .order-info p {
            margin: 8px 0;
        }
        .order-items {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 18px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #ff6b6b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-mixed">
                        <a href="index.php">
                            <span class="logo-letter logo-e">E</span>
                            <span class="logo-letter logo-r">R</span>
                        </a>
                    </div>
                    <a href="index.php" style="text-decoration: none;">
                        <span class="logo-subtitle">Мастерская одежды</span>
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php#collections">КОЛЛЕКЦИИ</a></li>
                        <li><a href="index.php#about">О НАС</a></li>
                        <li><a href="index.php#services">УСЛУГИ</a></li>
                        <li><a href="index.php#contact">КОНТАКТЫ</a></li>
                        <li><a href="track-order.php">ОТСЛЕДИТЬ</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="account.php"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?></a></li>
                            <li><a href="logout.php">ВЫЙТИ</a></li>
                        <?php else: ?>
                            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> ВХОД</a></li>
                            <li><a href="register.php">РЕГИСТРАЦИЯ</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-count"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <section class="track-section">
        <div class="container">
            <div class="track-container">
                <div class="track-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h1 class="track-title">ОТСЛЕЖИВАНИЕ ЗАКАЗА</h1>
                
                <form method="POST" class="search-form">
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Введите номер телефона" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn-search">НАЙТИ ЗАКАЗ</button>
                </form>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($orders) && !empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        switch($order['status']) {
                            case 'new': $statusClass = 'status-new'; $statusText = 'Новый'; break;
                            case 'processing': $statusClass = 'status-processing'; $statusText = 'В обработке'; break;
                            case 'shipped': $statusClass = 'status-shipped'; $statusText = 'Отправлен'; break;
                            case 'delivered': $statusClass = 'status-delivered'; $statusText = 'Доставлен'; break;
                            case 'cancelled': $statusClass = 'status-cancelled'; $statusText = 'Отменен'; break;
                            default: $statusClass = 'status-new'; $statusText = $order['status'];
                        }
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-number">Заказ №<?= htmlspecialchars($order['order_number']) ?></span>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                            <div class="order-info">
                                <p><strong>Дата заказа:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                                <p><strong>Получатель:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                                <p><strong>Телефон:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email'] ?: 'не указан') ?></p>
                                <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
                            </div>
                            
                            <?php
                            // Получаем товары заказа
                            $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                            $stmt->execute([$order['id']]);
                            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <div class="order-items">
                                <strong>Состав заказа:</strong>
                                <?php foreach ($items as $item): ?>
                                    <div class="order-item">
                                        <span><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?></span>
                                        <span><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?> ₽</span>
                                    </div>
                                <?php endforeach; ?>
                                <div class="order-total">
                                    <span>ИТОГО:</span>
                                    <span><?= number_format($order['total'], 0, '', ' ') ?> ₽</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (isset($order) && $order): ?>
                    <?php
                    $statusClass = '';
                    $statusText = '';
                    switch($order['status']) {
                        case 'new': $statusClass = 'status-new'; $statusText = 'Новый'; break;
                        case 'processing': $statusClass = 'status-processing'; $statusText = 'В обработке'; break;
                        case 'shipped': $statusClass = 'status-shipped'; $statusText = 'Отправлен'; break;
                        case 'delivered': $statusClass = 'status-delivered'; $statusText = 'Доставлен'; break;
                        case 'cancelled': $statusClass = 'status-cancelled'; $statusText = 'Отменен'; break;
                        default: $statusClass = 'status-new'; $statusText = $order['status'];
                    }
                    ?>
                    
                        
                        <?php
                        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
                        $stmt->execute([$order['id']]);
                        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="order-items">
                            <strong>Состав заказа:</strong>
                            <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <span><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?></span>
                                    <span><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?> ₽</span>
                                </div>
                            <?php endforeach; ?>
                            <div class="order-total">
                                <span>ИТОГО:</span>
                                <span><?= number_format($order['total'], 0, '', ' ') ?> ₽</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.php" class="back-link">← Вернуться на главную</a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
