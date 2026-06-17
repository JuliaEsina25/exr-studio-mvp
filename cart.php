<?php
session_start();

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
$cartCount = array_sum(array_column($cart, 'quantity'));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина | EXR Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        .cart-container { max-width: 1200px; margin: 120px auto 50px; padding: 0 20px; }
        .cart-table { width: 100%; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .cart-table th, .cart-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .cart-table th { background: #1a1a2e; color: white; }
        .cart-total { text-align: right; font-size: 24px; font-weight: 700; margin: 20px 0; color: #d4b896; }
        .empty-cart { text-align: center; padding: 50px; background: white; border-radius: 15px; }
        .remove-btn { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        .cart-image { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .product-info { display: flex; align-items: center; gap: 15px; }
        .btn-checkout { background: #d4b896; color: #1a1a2e; padding: 15px 30px; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-checkout:hover { background: #c2a575; }
        .quantity-input { width: 60px; padding: 5px; text-align: center; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="location.href='index.php'">
                    <div class="logo-mixed"><span class="logo-letter logo-e">E</span><span class="logo-letter logo-r">R</span></div>
                    <span class="logo-subtitle">Мастерская одежды</span>
                </div>
                <div class="cart-link"><a href="cart.php">🛒 Корзина <span class="cart-count"><?= $cartCount ?></span></a></div>
            </div>
        </div>
    </header>

    <div class="cart-container">
        <h1 class="section-title">Корзина</h1>
        <?php if (empty($cart)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart" style="font-size: 64px; color: #ddd;"></i>
                <h2>Ваша корзина пуста</h2>
                <a href="index.php" class="btn btn-primary">Перейти к покупкам</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr><th>Товар</th><th>Размер</th><th>Цена</th><th>Кол-во</th><th>Сумма</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $key => $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img class="cart-image" src="/exr-studio-mvp/Public/Pic/<?= htmlspecialchars($item['image'] ?? 'placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($item['size'] ?? 'Стандарт') ?></td>
                        <td><?= number_format($item['price'], 0, '', ' ') ?> ₽</td>
                        <td>
                            <form action="update-cart.php" method="POST">
                                <input type="hidden" name="key" value="<?= $key ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="99" class="quantity-input" onchange="this.form.submit()">
                            </form>
                        </td>
                        <td><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?> ₽</td>
                        <td>
                            <form action="remove-from-cart.php" method="POST">
                                <input type="hidden" name="key" value="<?= $key ?>">
                                <button type="submit" class="remove-btn">✕</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-total">Итого: <?= number_format($total, 0, '', ' ') ?> ₽</div>
            <div style="display: flex; justify-content: space-between; gap: 20px;">
                <a href="index.php" class="btn btn-outline">Продолжить покупки</a>
                <a href="checkout.php" class="btn-checkout">Оформить заказ</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
