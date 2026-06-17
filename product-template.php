<?php
if (!isset($product)) {
    header('Location: index.php');
    exit;
}

$productImages = $product['images'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> | EXR Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
    <style>
        .product-container { max-width: 1200px; margin: 120px auto 50px; padding: 0 20px; }
        .product-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .product-gallery { position: relative; }
        .main-image-container { position: relative; border-radius: 15px; overflow: hidden; background: #f5f5f5; margin-bottom: 15px; }
        .main-image { width: 100%; height: 450px; object-fit: cover; }
        .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 12px 18px; cursor: pointer; border-radius: 50%; font-size: 18px; transition: 0.3s; z-index: 10; }
        .carousel-btn:hover { background: #d4af37; }
        .prev-img { left: 15px; }
        .next-img { right: 15px; }
        .thumbnail-container { display: flex; gap: 10px; justify-content: center; margin-top: 15px; flex-wrap: wrap; }
        .thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; cursor: pointer; border: 2px solid transparent; transition: 0.3s; }
        .thumbnail.active { border-color: #d4af37; transform: scale(1.05); }
        .product-info { display: flex; flex-direction: column; justify-content: center; }
        .product-name { font-size: 32px; font-weight: 700; margin-bottom: 15px; color: #1a1a2e; }
        .product-price { font-size: 28px; font-weight: 700; color: #d4af37; margin-bottom: 20px; }
        .product-description { color: #666; line-height: 1.7; margin-bottom: 30px; }
        .quantity-selector { display: flex; align-items: center; gap: 15px; margin: 20px 0; }
        .quantity-btn { width: 35px; height: 35px; border-radius: 50%; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 18px; }
        .quantity-input { width: 60px; text-align: center; font-size: 16px; padding: 8px; border: 1px solid #ddd; border-radius: 8px; }
        .add-to-cart-btn { background: #d4af37; color: #1a1a2e; padding: 15px 30px; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; width: 100%; }
        .add-to-cart-btn:hover { background: #c4a02e; transform: translateY(-2px); }
        .back-link { display: inline-block; margin-top: 30px; color: #d4af37; text-decoration: none; }
        @media (max-width: 768px) { .product-wrapper { grid-template-columns: 1fr; } .main-image { height: 300px; } }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo"><div class="logo-mixed"><span class="logo-letter logo-e">E</span><span class="logo-letter logo-r">R</span></div><span class="logo-subtitle">Мастерская одежды</span></div>
                <div class="auth-links">
                    <?php if ($isLoggedIn): ?>
                        <span class="user-welcome">👋 <?= htmlspecialchars($userName) ?></span>
                        <a href="account.php">Личный кабинет</a>
                        <a href="logout.php">Выйти</a>
                    <?php else: ?>
                        <a href="login.php">Вход</a>
                        <a href="register.php">Регистрация</a>
                    <?php endif; ?>
                </div>
                <div class="cart-link"><a href="cart.php">🛒 Корзина <span class="cart-count"><?= $cartCount ?></span></a></div>
            </div>
        </div>
    </header>

    <div class="product-container">
        <div class="product-wrapper">
            <div class="product-gallery">
                <div class="main-image-container">
                    <img id="mainImage" class="main-image" src="/exr-studio-mvp/Public/Pic/<?= $productImages[0] ?>" alt="<?= $product['name'] ?>">
                    <?php if (count($productImages) > 1): ?>
                        <button class="carousel-btn prev-img" onclick="changeImage(-1)">&#10094;</button>
                        <button class="carousel-btn next-img" onclick="changeImage(1)">&#10095;</button>
                    <?php endif; ?>
                </div>
                <?php if (count($productImages) > 1): ?>
                <div class="thumbnail-container">
                    <?php foreach ($productImages as $index => $img): ?>
                    <img class="thumbnail <?= $index === 0 ? 'active' : '' ?>" src="/exr-studio-mvp/Public/Pic/<?= $img ?>" onclick="setImage(<?= $index ?>)" alt="Фото <?= $index+1 ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="product-info">
                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</div>
                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                <div class="quantity-selector">
                    <button class="quantity-btn" onclick="changeQuantity(-1)">−</button>
                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="99">
                    <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                </div>
                <form action="add-to-cart.php" method="POST" id="addToCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                    <input type="hidden" name="quantity" id="formQuantity" value="1">
                    <button type="submit" class="add-to-cart-btn">🛒 Добавить в корзину</button>
                </form>
                <a href="index.php#collections" class="back-link">← Вернуться к коллекциям</a>
            </div>
        </div>
    </div>

    <script>
        const images = <?= json_encode($productImages) ?>;
        let currentIndex = 0;
        function updateMainImage() {
            document.getElementById('mainImage').src = '/exr-studio-mvp/Public/Pic/' + images[currentIndex];
            document.querySelectorAll('.thumbnail').forEach((thumb, idx) => thumb.classList.toggle('active', idx === currentIndex));
        }
        function changeImage(d) { currentIndex = (currentIndex + d + images.length) % images.length; updateMainImage(); }
        function setImage(i) { currentIndex = i; updateMainImage(); }
        function changeQuantity(delta) {
            let qty = parseInt(document.getElementById('quantity').value) + delta;
            if (qty < 1) qty = 1;
            if (qty > 99) qty = 99;
            document.getElementById('quantity').value = qty;
            document.getElementById('formQuantity').value = qty;
        }
        document.getElementById('quantity').addEventListener('change', function() {
            let val = parseInt(this.value);
            if (isNaN(val) || val < 1) val = 1;
            if (val > 99) val = 99;
            this.value = val;
            document.getElementById('formQuantity').value = val;
        });
    </script>
</body>
</html>
