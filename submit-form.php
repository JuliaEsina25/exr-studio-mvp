<?php
// ============================================================
// ОБРАБОТЧИК ЗАЯВКИ С ГЛАВНОЙ СТРАНИЦЫ
// Файл: submit-form.php
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

// ============================================================
// 1. ПОЛУЧАЕМ ДАННЫЕ ИЗ ФОРМЫ
// ============================================================
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// ============================================================
// 2. ВАЛИДАЦИЯ
// ============================================================
$errors = [];

if (empty($name)) {
    $errors[] = 'Введите ваше имя';
}

if (empty($phone)) {
    $errors[] = 'Введите телефон';
}

if (empty($message)) {
    $errors[] = 'Опишите ваш проект';
}

if (!empty($errors)) {
    $_SESSION['form_error'] = implode('<br>', $errors);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

// ============================================================
// 3. ФОРМИРУЕМ ЗАЯВКУ
// ============================================================
$order_items = [[
    'id' => 0,
    'name' => 'Заявка с сайта',
    'price' => 0,
    'quantity' => 1,
    'size' => 'Не указан',
    'message' => $message
]];

$items_json = json_encode($order_items, JSON_UNESCAPED_UNICODE);
$order_number = 'REQ-' . date('Ymd') . '-' . rand(100, 999);

// ID пользователя из сессии (если авторизован)
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id && empty($email)) {
    $email = $_SESSION['user_email'] ?? '';
}

// Адрес доставки (для заявки — заглушка)
$customer_address = 'Заявка с сайта';

// ============================================================
// 4. СОХРАНЯЕМ В БД
// ============================================================
try {
    // Проверяем, есть ли колонка user_id в таблице orders
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
    $has_user_id = $check->num_rows > 0;
    
    if ($has_user_id) {
        // ============================================================
        // ВАРИАНТ 1: С user_id (7 параметров)
        // ============================================================
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
        $total = 0;
        $stmt->bind_param(
            "isssssss",
            $user_id,
            $order_number,
            $name,
            $phone,
            $email,
            $customer_address,
            $total,
            $items_json
        );
    } else {
        // ============================================================
        // ВАРИАНТ 2: Без user_id (7 параметров)
        // ============================================================
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
        $total = 0;
        $stmt->bind_param(
            "sssssss",
            $order_number,
            $name,
            $phone,
            $email,
            $customer_address,
            $total,
            $items_json
        );
    }
    
    if ($stmt->execute()) {
        $_SESSION['form_success'] = '✅ Ваша заявка отправлена! Мы свяжемся с вами в ближайшее время.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    } else {
        throw new Exception("Ошибка: " . $stmt->error);
    }
    
} catch (Exception $e) {
    $_SESSION['form_error'] = '❌ Ошибка при отправке: ' . $e->getMessage();
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}
?>