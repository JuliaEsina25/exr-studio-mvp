<?php
session_start();
require_once __DIR__ . '/config/database.php';

$products = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT id, name, price, description, image_url FROM products WHERE is_active = 1");
    $products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EXR Studio</title>
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
</head>
<body>
    <h1>Коллекции</h1>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <?php foreach ($products as $p): ?>
            <div style="border:1px solid #ccc; padding:15px; width:250px;">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="/exr-studio-mvp/Public/Pic/<?= $p['image_url'] ?>" style="width:100%; height:200px; object-fit:cover;">
                    <h3><?= $p['name'] ?></h3>
                    <p><?= number_format($p['price'], 0, '', ' ') ?> ₽</p>
                </a>
                <form action="add-to-cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= $p['name'] ?>">
                    <input type="hidden" name="product_price" value="<?= $p['price'] ?>">
                    <button type="submit">В корзину</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
