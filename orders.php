<?php
// ============================================================
// СТРАНИЦА ЗАКАЗОВ (ИСТОРИЯ ЗАКАЗОВ ИЗ БД)
// ============================================================

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$user_id = $_SESSION['user_id'];
$orders = [];

try {
    // ============================================================
    // 1. ПОЛУЧАЕМ ИНФОРМАЦИЮ О ПОЛЬЗОВАТЕЛЕ
    // ============================================================
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // ============================================================
    // 2. ПОЛУЧАЕМ ЗАКАЗЫ ИЗ БД
    // ============================================================
    // Ищем заказы по user_id ИЛИ по email
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? OR customer_email = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("is", $user_id, $user['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Декодируем JSON с товарами
        $items = json_decode($row['items'], true);
        
        // Если товары не в JSON, пробуем другие форматы
        if (!is_array($items)) {
            // Если items хранятся как сериализованный массив
            if (is_string($row['items']) && strpos($row['items'], 'a:') === 0) {
                $items = unserialize($row['items']);
            } else {
                $items = [];
            }
        }
        
        // Если items не массив или пустой, пробуем разобрать как строку
        if (!is_array($items) || empty($items)) {
            // Пытаемся извлечь из JSON строки
            $decoded = json_decode($row['items'], true);
            if (is_array($decoded) && !empty($decoded)) {
                $items = $decoded;
            } else {
                // Создаём заглушку
                $items = [['name' => 'Товар', 'quantity' => 1, 'price' => $row['total']]];
            }
        }
        
        $orders[] = [
            'id' => $row['id'],
            'number' => $row['order_number'],
            'date' => $row['created_at'],
            'total' => $row['total'],
            'status' => $row['status'],
            'items' => $items,
            'customer_name' => $row['customer_name'],
            'customer_phone' => $row['customer_phone'],
            'customer_address' => $row['customer_address']
        ];
    }
    
} catch (Exception $e) {
    $error = "Ошибка загрузки заказов: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы | EXR Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        /* Фон как в Hero секции */
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('/exr-studio-mvp/Public/Pic/Hero.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        .orders-container {
            max-width: 1000px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .orders-header h1 {
            color: white;
            font-size: 36px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .home-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 30px;
            transition: 0.3s;
        }
        
        .home-link:hover {
            background: #d4af37;
            color: #1a1a2e;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-number {
            font-weight: bold;
            color: #d4af37;
            font-size: 18px;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce5ff; color: #004085; }
        .status-in_progress { background: #d6d8db; color: #383d41; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-items {
            margin-bottom: 15px;
        }
        
        .order-items .item-row {
            padding: 5px 0;
            color: #333;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-items .item-row:last-child {
            border-bottom: none;
        }
        
        .order-total {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            color: #d4af37;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .order-address {
            font-size: 14px;
            color: #666;
            margin: 10px 0;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 40px;
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .empty-orders i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-orders p {
            color: #666;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #d4af37;
            color: #1a1a2e;
        }
        
        .btn-primary:hover {
            background: #c4a02e;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.2);
            border: 1px solid white;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #d4af37;
            border-color: #d4af37;
            color: #1a1a2e;
        }
        
        .continue-shopping {
            text-align: center;
            margin-top: 30px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .orders-header {
                flex-direction: column;
                text-align: center;
            }
            .order-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="orders-container">
            <div class="orders-header">
                <h1><i class="fas fa-box"></i> Мои заказы</h1>
                <a href="index.php" class="home-link"><i class="fas fa-home"></i> На главную</a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <p>У вас пока нет заказов</p>
                    <p style="font-size: 14px; color: #999;">Сделайте первый заказ в нашем каталоге!</p>
                    <a href="index.php#collections" class="btn btn-primary" style="margin-top: 10px;">
                        <i class="fas fa-arrow-right"></i> Перейти в каталог
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-number">Заказ №<?= htmlspecialchars($order['number']) ?></span>
                        <span>
                            <span class="order-status status-<?= $order['status'] ?>">
                                <?php
                                $statuses = [
                                    'pending' => 'В обработке',
                                    'confirmed' => 'Подтверждён',
                                    'in_progress' => 'В работе',
                                    'shipped' => 'Отправлен',
                                    'delivered' => 'Доставлен',
                                    'cancelled' => 'Отменён'
                                ];
                                echo $statuses[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                            <span class="order-date" style="margin-left: 15px;">
                                <i class="far fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($order['date'])) ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="order-items">
                        <?php if (is_array($order['items']) && count($order['items']) > 0): ?>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="item-row">
                                    • <?= htmlspecialchars($item['name'] ?? 'Товар') ?> 
                                    <?php if (isset($item['size']) && $item['size']): ?>
                                        (размер: <?= htmlspecialchars($item['size']) ?>)
                                    <?php endif; ?>
                                    x <?= $item['quantity'] ?? 1 ?> 
                                    — <?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, '', ' ') ?> ₽
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="item-row">Товары не указаны</div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($order['customer_address']) && $order['customer_address']): ?>
                        <div class="order-address">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?= htmlspecialchars($order['customer_address']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="order-total">
                        Итого: <?= number_format($order['total'], 0, '', ' ') ?> ₽
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="continue-shopping">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Продолжить покупки</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>