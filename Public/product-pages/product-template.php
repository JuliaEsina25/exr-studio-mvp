<?php
if (!isset($product)) {
    header('Location: ../../index.php');
    exit;
}

$productImages = $product['images'];
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? $_SESSION['full_name'] ?? 'Гость';
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$baseLength = $product['base_length'] ?? 110;
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
        .product-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        .product-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .product-gallery {
            position: relative;
        }
        .main-image-container {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            background: #f5f5f5;
            margin-bottom: 15px;
        }
        .main-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
        }
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            padding: 12px 18px;
            cursor: pointer;
            border-radius: 50%;
            font-size: 18px;
            transition: 0.3s;
        }
        .carousel-btn:hover {
            background: #d4b896;
        }
        .prev-img {
            left: 15px;
        }
        .next-img {
            right: 15px;
        }
        .thumbnail-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: 0.3s;
        }
        .thumbnail.active {
            border-color: #d4b896;
        }
        .product-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1a1a2e;
        }
        .product-price {
            font-size: 28px;
            font-weight: 700;
            color: #d4b896;
            margin-bottom: 20px;
        }
        .product-description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        .size-selector {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 12px;
        }
        .size-selector label {
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
            color: #1a1a2e;
        }
        .size-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .size-btn {
            width: 55px;
            padding: 12px 0;
            text-align: center;
            background: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }
        .size-btn:hover {
            border-color: #d4b896;
        }
        .size-btn.selected {
            background: #d4b896;
            border-color: #d4b896;
            color: #1a1a2e;
        }
        .fabric-calculator {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #eee;
        }
        .fabric-calculator h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1a1a2e;
        }
        .fabric-field {
            margin-bottom: 12px;
        }
        .fabric-field label {
            display: block;
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .fabric-field select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: white;
        }
        .calc-btn {
            background: #1a1a2e;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        .calc-btn:hover {
            background: #d4b896;
            color: #1a1a2e;
        }
        .fabric-result {
            background: #1a1a2e;
            color: #d4b896;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-top: 15px;
        }
        .fabric-result span {
            font-size: 24px;
            font-weight: 700;
            display: block;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }
        .quantity-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            font-size: 18px;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
            font-size: 16px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .add-to-cart-btn {
            background: #d4b896;
            color: #1a1a2e;
            padding: 15px 30px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .add-to-cart-btn:hover {
            background: #c2a575;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #d4b896;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .product-wrapper {
                grid-template-columns: 1fr;
            }
            .main-image {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="location.href='../../index.php'">
                    <div class="logo-mixed">
                        <span class="logo-letter logo-e">E</span>
                        <span class="logo-letter logo-r">R</span>
                    </div>
                    <span class="logo-subtitle">Мастерская одежды</span>
                </div>
                <div class="auth-links">
                    <?php if ($isLoggedIn): ?>
                        <span class="user-welcome">👋 <?= htmlspecialchars($userName) ?></span>
                        <a href="../../account.php">Личный кабинет</a>
                        <a href="../../logout.php">Выйти</a>
                    <?php else: ?>
                        <a href="../../login.php">Вход</a>
                        <a href="../../register.php">Регистрация</a>
                    <?php endif; ?>
                </div>
                <div class="cart-link">
                    <a href="../../cart.php">🛒 Корзина <span class="cart-count"><?= $cartCount ?></span></a>
                </div>
            </div>
        </div>
    </header>

    <div class="product-container">
        <div class="product-wrapper">
            <div class="product-gallery">
                <div class="main-image-container">
                    <img id="mainImage" class="main-image" src="/exr-studio-mvp/Public/Pic/<?= str_replace('images/', '', $productImages[0]) ?>" alt="<?= $product['name'] ?>">
                    <?php if (count($productImages) > 1): ?>
                        <button class="carousel-btn prev-img" onclick="changeImage(-1)">&#10094;</button>
                        <button class="carousel-btn next-img" onclick="changeImage(1)">&#10095;</button>
                    <?php endif; ?>
                </div>
                <?php if (count($productImages) > 1): ?>
                <div class="thumbnail-container">
                    <?php foreach ($productImages as $index => $img): ?>
                        <img class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                        src="/exr-studio-mvp/Public/Pic/<?= $img ?>" 
                        onclick="setImage(<?= $index ?>)"  
                        alt="Фото">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</div>
                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                
                <div class="size-selector">
                    <label>Выберите размер:</label>
                    <div class="size-buttons" id="sizeButtons">
                        <div class="size-btn" data-size="40">40</div>
                        <div class="size-btn" data-size="42">42</div>
                        <div class="size-btn" data-size="44">44</div>
                        <div class="size-btn selected" data-size="46">46</div>
                        <div class="size-btn" data-size="48">48</div>
                        <div class="size-btn" data-size="50">50</div>
                        <div class="size-btn" data-size="52">52</div>
                        <div class="size-btn" data-size="54">54</div>
                    </div>
                    <input type="hidden" id="selectedSize" value="46">
                </div>
                
                <div class="fabric-calculator">
                    <h3>Расчёт ткани</h3>
                    <div class="fabric-field">
                        <label>Ширина ткани:</label>
                        <select id="fabricWidth">
                            <option value="140">140 см</option>
                            <option value="150" selected>150 см</option>
                            <option value="220">220 см</option>
                        </select>
                    </div>
                    <div class="fabric-field">
                        <label>Тип ткани:</label>
                        <select id="fabricType">
                            <option value="stretch">Стрейч</option>
                            <option value="nonstretch" selected>Не стрейч</option>
                        </select>
                    </div>
                    <input type="hidden" id="productLength" value="<?= $baseLength ?>">
                    <button class="calc-btn" onclick="calculateFabric()">Рассчитать</button>
                    <div class="fabric-result">Расход: <span id="fabricAmount">—</span> м</div>
                </div>
                
                <div class="quantity-selector">
                    <button class="quantity-btn" onclick="changeQuantity(-1)">−</button>
                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="99">
                    <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                </div>
                
                <form action="../../add-to-cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                    <input type="hidden" name="quantity" id="formQuantity" value="1">
                    <input type="hidden" name="size" id="formSize" value="46">
                    <input type="hidden" name="product_image" value="<?= $productImages[0] ?>">
                    <button type="submit" class="add-to-cart-btn">🛒 Добавить в корзину</button>
                </form>
                
                <a href="../../index.php" class="back-link">← Вернуться к коллекциям</a>
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
        
        function changeImage(d) { 
            currentIndex = (currentIndex + d + images.length) % images.length; 
            updateMainImage(); 
        }
        
        function setImage(i) { 
            currentIndex = i; 
            updateMainImage(); 
        }
        
        function changeQuantity(delta) {
            let qty = parseInt(document.getElementById('quantity').value) + delta;
            if (qty < 1) qty = 1;
            if (qty > 99) qty = 99;
            document.getElementById('quantity').value = qty;
            document.getElementById('formQuantity').value = qty;
        }
        
        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selectedSize').value = this.getAttribute('data-size');
                document.getElementById('formSize').value = this.getAttribute('data-size');
                calculateFabric();
            });
        });
        
        function calculateFabric() {
            const size = parseInt(document.getElementById('selectedSize').value);
            const fabricWidth = parseInt(document.getElementById('fabricWidth').value);
            const fabricType = document.getElementById('fabricType').value;
            let productLength = parseInt(document.getElementById('productLength').value);
            if (isNaN(productLength)) productLength = 110;
            
            let sizeCoeff = size >= 52 ? 1.3 : (size >= 48 ? 1.15 : (size >= 44 ? 1.03 : 0.92));
            let fabricCoeff = fabricType === 'stretch' ? 1.05 : 1.15;
            let consumption = (productLength / 100) * sizeCoeff;
            if (fabricWidth <= 140) consumption *= 1.35;
            else if (fabricWidth >= 200) consumption *= 0.85;
            consumption = Math.ceil(consumption * fabricCoeff * 10) / 10 + 0.15;
            document.getElementById('fabricAmount').innerHTML = consumption.toFixed(1);
        }
        
        document.getElementById('fabricWidth').addEventListener('change', calculateFabric);
        document.getElementById('fabricType').addEventListener('change', calculateFabric);
        window.addEventListener('DOMContentLoaded', calculateFabric);
    </script>
</body>
</html>
