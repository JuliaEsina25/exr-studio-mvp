<?php
// ============================================================
// ЛИЧНЫЙ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ
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
    // Информация о пользователе
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // Заказы по user_id ИЛИ по email
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? OR customer_email = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("is", $user_id, $user['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $items = json_decode($row['items'], true);
        if (!is_array($items)) {
            $items = [];
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
    $error = "Ошибка: " . $e->getMessage();
}

// Количество товаров в корзине
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — EXR Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        /* ============================================================
           СТИЛИ ДЛЯ ЛИЧНОГО КАБИНЕТА
           ============================================================ */
        
        /* Фон как на главной */
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('/exr-studio-mvp/Public/Pic/Hero.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        
        /* ============================================================
           ХЕДЕР КАК НА ГЛАВНОЙ
           ============================================================ */
        header {
            background: rgba(26, 26, 46, 0.95);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Логотип */
        .logo {
            display: flex;
            align-items: center;
            cursor: pointer;
            gap: 10px;
        }
        
        .logo-mixed {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        
        .logo-letter {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 800;
            line-height: 1;
        }
        
        .logo-e {
            color: #d4af37;
        }
        
        .logo-r {
            color: white;
        }
        
        .logo-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-left: 5px;
        }
        
        /* Навигация */
        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
            padding: 0;
        }
        
        nav ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            transition: color 0.3s;
            letter-spacing: 0.5px;
        }
        
        nav ul li a:hover {
            color: #d4af37;
        }
        
        /* Действия в хедере */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header-actions a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 18px;
        }
        
        .header-actions a:hover {
            color: #d4af37;
        }
        
        .cart-link {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #d4af37;
            color: #1a1a2e;
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-link {
            color: #d4af37 !important;
        }
        
        .user-link i {
            font-size: 22px;
        }
        
        .logout-link {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 18px !important;
        }
        
        .logout-link:hover {
            color: #dc3545 !important;
        }
        
        .login-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 30px;
            color: white !important;
            font-size: 13px !important;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .login-btn:hover {
            background: #d4af37;
            border-color: #d4af37;
            color: #1a1a2e !important;
        }
        
        .register-btn {
            padding: 8px 20px;
            background: #d4af37;
            border: 1px solid #d4af37;
            border-radius: 30px;
            color: #1a1a2e !important;
            font-size: 13px !important;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .register-btn:hover {
            background: #c4a02e;
            border-color: #c4a02e;
        }
        
        /* ============================================================
           ОСНОВНОЙ КОНТЕНТ
           ============================================================ */
        .account-container {
            max-width: 1000px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        
        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .account-header h1 {
            font-size: 36px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
        }
        
        .account-header h1 i {
            color: #d4af37;
        }
        
        .account-header .user-info {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .account-header .user-info strong {
            color: white;
        }
        
        .logout-btn {
            padding: 10px 25px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
        
        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .profile-section h2 {
            font-size: 22px;
            color: #2D2D2D;
            margin-bottom: 20px;
        }
        
        .profile-section h2 i {
            color: #B8956E;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .profile-item {
            padding: 12px 0;
            border-bottom: 1px solid #F5E6D3;
        }
        
        .profile-item label {
            font-size: 13px;
            color: #999;
            display: block;
            margin-bottom: 3px;
        }
        
        .profile-item span {
            font-size: 16px;
            font-weight: 500;
            color: #2D2D2D;
        }
        
        .orders-section h2 {
            font-size: 22px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        
        .orders-section h2 i {
            color: #d4af37;
        }
        
        .order-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            border-left: 4px solid #d4af37;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-number {
            font-weight: 700;
            font-size: 18px;
            color: #2D2D2D;
        }
        
        .order-number i {
            color: #d4af37;
            margin-right: 5px;
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
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .order-details strong {
            color: #2D2D2D;
        }
        
        .order-items {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #F5E6D3;
            font-size: 14px;
        }
        
        .order-items .item-row {
            padding: 3px 0;
            color: #333;
        }
        
        .order-items .item-row i {
            color: #B8956E;
            margin-right: 5px;
        }
        
        .no-orders {
            text-align: center;
            padding: 50px 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .no-orders i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }
        
        .no-orders p {
            color: #666;
            font-size: 18px;
        }
        
        .no-orders .sub-text {
            font-size: 14px;
            color: #999;
            margin-top: 10px;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 12px 30px;
            background: #B8956E;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: #A07D5A;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(184, 149, 110, 0.4);
        }
        
        .btn-primary i {
            margin-right: 8px;
        }
        
        /* ============================================================
           ФУТЕР
           ============================================================ */
        footer {
            background: rgba(26, 26, 46, 0.95);
            padding: 40px 0 20px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .footer-logo .logo-letter {
            font-size: 28px;
        }
        
        .footer-logo .logo-subtitle {
            font-size: 10px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #d4af37;
        }
        
        .footer-social {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        .footer-social a {
            color: rgba(255, 255, 255, 0.5);
            font-size: 20px;
            transition: color 0.3s;
        }
        
        .footer-social a:hover {
            color: #d4af37;
        }
        
        .footer-copy {
            grid-column: 1 / -1;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 10px;
        }
        
        .footer-copy p {
            color: rgba(255, 255, 255, 0.3);
            font-size: 12px;
            margin: 5px 0;
        }
        
        /* ============================================================
           АДАПТИВНОСТЬ
           ============================================================ */
        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
                gap: 10px;
                justify-content: center;
            }
            
            nav ul {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            nav ul li a {
                font-size: 12px;
            }
            
            .account-container {
                margin-top: 160px;
            }
            
            .account-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .footer-social {
                justify-content: center;
            }
            
            .footer-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- ============================================================
    ХЕДЕР КАК НА ГЛАВНОЙ
    ============================================================ -->
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

    <!-- ============================================================
    ЛИЧНЫЙ КАБИНЕТ
    ============================================================ -->
    <div class="account-container">
        <div class="account-header">
            <div>
                <h1><i class="fas fa-user-circle"></i> Личный кабинет</h1>
                <div class="user-info">
                    <strong><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></strong> 
                    · <?= htmlspecialchars($user['email']) ?>
                </div>
            </div>
            <a href="/exr-studio-mvp/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Выйти
            </a>
        </div>
        
        <!-- Профиль -->
        <div class="profile-section">
            <h2><i class="fas fa-id-card"></i> Мои данные</h2>
            <div class="profile-grid">
                <div class="profile-item">
                    <label>Логин</label>
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
                <div class="profile-item">
                    <label>Email</label>
                    <span><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div class="profile-item">
                    <label>Полное имя</label>
                    <span><?= htmlspecialchars($user['full_name'] ?: 'Не указано') ?></span>
                </div>
                <div class="profile-item">
                    <label>Дата регистрации</label>
                    <span><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Заказы -->
        <div class="orders-section">
            <h2><i class="fas fa-shopping-bag"></i> Мои заказы (<?= count($orders) ?>)</h2>
            
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-number"><i class="fas fa-receipt"></i> Заказ №<?= htmlspecialchars($order['number']) ?></span>
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
                    </div>
                    
                    <div class="order-details">
                        <div><strong><i class="far fa-calendar-alt"></i> Дата:</strong> <?= date('d.m.Y H:i', strtotime($order['date'])) ?></div>
                        <div><strong><i class="fas fa-credit-card"></i> Сумма:</strong> <?= number_format($order['total'], 0, ' ', ' ') ?> ₽</div>
                        <div><strong><i class="fas fa-user"></i> Получатель:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                        <div><strong><i class="fas fa-phone"></i> Телефон:</strong> <?= htmlspecialchars($order['customer_phone']) ?></div>
                        <div style="grid-column: 1 / -1;"><strong><i class="fas fa-map-marker-alt"></i> Адрес:</strong> <?= htmlspecialchars($order['customer_address']) ?></div>
                    </div>
                    
                    <?php if (is_array($order['items']) && count($order['items']) > 0): ?>
                        <div class="order-items">
                            <strong><i class="fas fa-list"></i> Состав заказа:</strong>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="item-row">
                                    <i class="fas fa-tshirt"></i> <?= htmlspecialchars($item['name'] ?? 'Товар') ?> 
                                    (размер: <?= htmlspecialchars($item['size'] ?? 'Стандарт') ?>)
                                    × <?= $item['quantity'] ?? 1 ?> шт.
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <p>У вас пока нет заказов</p>
                    <p class="sub-text">Сделайте первый заказ в нашем каталоге!</p>
                    <br>
                    <a href="/exr-studio-mvp/index.php#collections" class="btn-primary">
                        <i class="fas fa-arrow-right"></i> Перейти в каталог
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================
    ФУТЕР КАК НА ГЛАВНОЙ
    ============================================================ -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="logo-letter logo-e">E</span>
                    <span class="logo-letter logo-r">R</span>
                    <span class="logo-subtitle">Мастерская одежды</span>
                </div>
                 <div class="footer-social">
                    <a href="#"><i class="fab fa-telegram"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-vk"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
                <div class="footer-copy">
                    <p>&copy; 2026 EXR Studio. Все права защищены.</p>
                    <p>Разработано с ❤️</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>