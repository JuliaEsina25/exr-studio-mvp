<?php
session_start();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
    switch ($action) {
        case 'increase':
            $_SESSION['cart'][$product_id]['quantity']++;
            break;
        case 'decrease':
            $_SESSION['cart'][$product_id]['quantity']--;
            if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
        case 'remove':
            unset($_SESSION['cart'][$product_id]);
            break;
    }
}

header('Location: cart.php');
exit;
?>
