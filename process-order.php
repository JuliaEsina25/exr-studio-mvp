<?php
// ============================================================
// ОБРАБОТЧИК ЗАЯВКИ НА ИЗГОТОВЛЕНИЕ
// ============================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config/database.php';

// Проверяем, что пришли данные
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Получаем данные
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$customer_address = trim($_POST['customer_address'] ?? '');
$size = trim($_POST['size'] ?? '');
$product_name = trim($_POST['product_name'] ?? '');
$product_price = (float)($_POST['product_price'] ?? 0);
$product_id = (int)($_POST['product_id'] ?? 0);
$fabric_consumption = trim($_POST['fabric_consumption'] ?? '0');
$comment = trim($_POST['comment'] ?? '');

// ID пользователя из сессии
$user_id = $_SESSION['user_id'] ?? null;

// Если пользователь авторизован, но email не указан в форме
if ($user_id && empty($customer_email)) {
    $customer_email = $_SESSION['user_email'] ?? '';
}

// Валидация
$errors = [];
if (empty($customer_name)) $errors[] = 'Введите ваше имя';
if (empty($customer_phone)) $errors[] = 'Введите телефон';
if (empty($customer_address)) $errors[] = 'Введите адрес';
if (empty($size)) $errors[] = 'Выберите размер';

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

// Формируем заказ
$order_items = [[
    'id' => $product_id,
    'name' => $product_name,
    'price' => $product_price,
    'quantity' => 1,
    'size' => $size,
    'fabric_consumption' => $fabric_consumption
]];

$items_json = json_encode($order_items, JSON_UNESCAPED_UNICODE);
$order_number = 'ORD-' . date('Ymd') . '-' . rand(100, 999);
$total = $product_price;

try {
    // Проверяем, есть ли колонка user_id в таблице orders
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
    $has_user_id = $check->num_rows > 0;
    
    if ($has_user_id) {
        // Если есть user_id — сохраняем с ним
        $sql = "INSERT INTO orders (
                    user_id,
                    order_number,
                    customer_name,
                    customer_phone,
                    customer_email,
                    customer_address,
                    total,
                    items,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssss",
            $user_id,
            $order_number,
            $customer_name,
            $customer_phone,
            $customer_email,
            $customer_address,
            $total,
            $items_json
        );
    } else {
        // Если нет user_id — сохраняем без него
        $sql = "INSERT INTO orders (
                    order_number,
                    customer_name,
                    customer_phone,
                    customer_email,
                    customer_address,
                    total,
                    items,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssss",
            $order_number,
            $customer_name,
            $customer_phone,
            $customer_email,
            $customer_address,
            $total,
            $items_json
        );
    }
    
    if ($stmt->execute()) {
        // Очищаем корзину
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
        
        // Перенаправляем на страницу успеха
        header('Location: success.php?order=' . $order_number);
        exit;
    } else {
        throw new Exception("Ошибка: " . $stmt->error);
    }
    
} catch (Exception $e) {
    // Показываем ошибку для отладки
    echo "<h3>Ошибка при сохранении заказа:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql) . "</p>";
    echo "<p><strong>Данные:</strong></p>";
    echo "<pre>";
    print_r([
        'user_id' => $user_id,
        'order_number' => $order_number,
        'customer_name' => $customer_name,
        'customer_phone' => $customer_phone,
        'customer_email' => $customer_email,
        'customer_address' => $customer_address,
        'total' => $total,
        'items' => $items_json
    ]);
    echo "</pre>";
    exit;
}
?>