<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$product_id = $_POST['product_id'] ?? null;
$product_name = $_POST['product_name'] ?? '';
$product_price = $_POST['product_price'] ?? 0;
$product_image = $_POST['product_image'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 1);
$size = $_POST['size'] ?? 'Стандарт';
$fabric_consumption = $_POST['fabric_consumption'] ?? '';

if ($product_id) {
    $key = $product_id . '_' . $size;
    
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => $quantity,
            'size' => $size,
            'fabric_consumption' => $fabric_consumption
        ];
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
?>
