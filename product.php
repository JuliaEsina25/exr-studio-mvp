<?php
// product.php
if (!isset($product)) {
    // Если $product не передан, пробуем получить из БД
    require_once 'config/database.php';
    $product_id = $_GET['id'] ?? 1;
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        header('Location: index.php');
        exit;
    }
    
    // Добавляем массив изображений
    $product['images'] = [$product['image'] ?? 'placeholder.jpg'];
}

$productImages = $product['images'];
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #fafafa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        header { background: #1a1a2e; padding: 15px 0; position: fixed; width: 100%; top: 0; z-index: 1000; }
        .header-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .logo { display: flex; align-items: baseline; gap: 10px; }
        .logo-mixed { display: flex; gap: 2px; }
        .logo-letter { font-size: 28px; font-weight: 800; }
        .logo-e { color: #d4af37; }
        .logo-r { color: white; }
        .logo-subtitle { color: #888; font-size: 12px; }
        .auth-links a, .cart-link a { color: white; text-decoration: none; margin-left: 15px; transition: 0.3s; }
        .auth-links a:hover, .cart-link a:hover { color: #d4af37; }
        .user-welcome { color: #d4af37; margin-right: 10px; }
        .cart-count { background: #d4af37; color: #1a1a2e; padding: 2px 6px; border-radius: 50%; font-size: 12px; }
        
        .product-container { max-width: 1200px; margin: 120px auto 50px; padding: 0 20px; }
        .product-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .main-image-container { position: relative; border-radius: 15px; overflow: hidden; background: #f5f5f5; margin-bottom: 15px; }
        .main-image { width: 100%; height: 450px; object-fit: cover; }
        .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 12px 18px; cursor: pointer; border-radius: 50%; font-size: 18px; transition: 0.3s; }
        .carousel-btn:hover { background: #d4af37; }
        .prev-img { left: 15px; }
        .next-img { right: 15px; }
        .thumbnail-container { display: flex; gap: 10px; justify-content: center; margin-top: 15px; flex-wrap: wrap; }
        .thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; cursor: pointer; border: 2px solid transparent; transition: 0.3s; }
        .thumbnail.active { border-color: #d4af37; }
        
        .product-name { font-size: 32px; font-weight: 700; margin-bottom: 15px; color: #1a1a2e; }
        .product-price { font-size: 28px; font-weight: 700; color: #d4af37; margin-bottom: 20px; }
        .product-description { color: #666; line-height: 1.7; margin-bottom: 30px; }
        
        /* ВЫБОР РАЗМЕРА ОДЕЖДЫ */
        .size-selector { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 12px; }
        .size-selector label { font-weight: 600; display: block; margin-bottom: 10px; color: #1a1a2e; }
        .size-buttons { display: flex; flex-wrap: wrap; gap: 10px; }
        .size-btn { width: 55px; padding: 12px 0; text-align: center; background: white; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: 0.3s; font-weight: 600; }
        .size-btn:hover { border-color: #d4af37; }
        .size-btn.selected { background: #d4af37; border-color: #d4af37; color: #1a1a2e; }
        
        /* КАЛЬКУЛЯТОР ТКАНИ */
        .fabric-calculator { background: #f9f9f9; border-radius: 15px; padding: 20px; margin: 20px 0; border: 1px solid #eee; }
        .fabric-calculator h3 { font-size: 18px; margin-bottom: 15px; color: #1a1a2e; display: flex; align-items: center; gap: 10px; }
        .fabric-calculator h3 i { color: #d4af37; }
        .fabric-field { margin-bottom: 12px; }
        .fabric-field label { display: block; font-weight: 500; color: #333; margin-bottom: 5px; font-size: 13px; }
        .fabric-field select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background: white; }
        .calc-btn { background: #1a1a2e; color: white; border: none; padding: 12px 20px; border-radius: 25px; cursor: pointer; transition: 0.3s; font-size: 14px; width: 100%; margin-top: 10px; }
        .calc-btn:hover { background: #d4af37; color: #1a1a2e; }
        .fabric-result { background: #1a1a2e; color: #d4af37; padding: 15px; border-radius: 10px; text-align: center; margin-top: 15px; }
        .fabric-result span { font-size: 24px; font-weight: 700; display: block; }
        
        .quantity-selector { display: flex; align-items: center; gap: 15px; margin: 20px 0; }
        .quantity-btn { width: 35px; height: 35px; border-radius: 50%; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 18px; }
        .quantity-input { width: 60px; text-align: center; font-size: 16px; padding: 8px; border: 1px solid #ddd; border-radius: 8px; }
        .add-to-cart-btn { background: #d4af37; color: #1a1a2e; padding: 15px 30px; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; width: 100%; }
        .add-to-cart-btn:hover { background: #c4a02e; transform: translateY(-2px); }
        .back-link { display: inline-block; margin-top: 30px; color: #d4af37; text-decoration: none; }
        
        @media (max-width: 768px) { 
            .product-wrapper { grid-template-columns: 1fr; } 
            .main-image { height: 300px; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-mixed"><span class="logo-letter logo-e">E</span><span class="logo-letter logo-r">R</span></div>
                    <span class="logo-subtitle">Мастерская одежды</span>
                </div>
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
                    <img class="thumbnail <?= $index === 0 ? 'active' : '' ?>" src="/exr-studio-mvp/Public/Pic/<?= $img ?>" onclick="setImage(<?= $index ?>)" alt="Фото">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</div>
                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                
                <!-- === ВЫБОР РАЗМЕРА ОДЕЖДЫ === -->
                <div class="size-selector">
                    <label><i class="fas fa-arrows-alt-h"></i> Выберите размер:</label>
                    <div class="size-buttons" id="sizeButtons">
                        <div class="size-btn" data-size="40">40 (XS)</div>
                        <div class="size-btn" data-size="42">42 (S)</div>
                        <div class="size-btn" data-size="44">44 (S-M)</div>
                        <div class="size-btn selected" data-size="46">46 (M)</div>
                        <div class="size-btn" data-size="48">48 (M-L)</div>
                        <div class="size-btn" data-size="50">50 (L)</div>
                        <div class="size-btn" data-size="52">52 (XL)</div>
                        <div class="size-btn" data-size="54">54 (XXL)</div>
                    </div>
                    <input type="hidden" id="selectedSize" value="46">
                </div>
                
                <!-- === РАСЧЁТ ТКАНИ === -->
                <div class="fabric-calculator">
                    <h3><i class="fas fa-cut"></i> Расчёт ткани на изделие</h3>
                    <div class="fabric-field">
                        <label><i class="fas fa-tshirt"></i> Ширина ткани:</label>
                        <select id="fabricWidth">
                            <option value="140">140 см (узкая)</option>
                            <option value="150" selected>150 см (стандарт)</option>
                            <option value="220">220 см (широкая)</option>
                        </select>
                    </div>
                    <div class="fabric-field">
                        <label><i class="fas fa-hand-peace"></i> Тип ткани:</label>
                        <select id="fabricType">
                            <option value="stretch">Стрейч (эластичная)</option>
                            <option value="nonstretch" selected>Не стрейч (плотная)</option>
                        </select>
                    </div>
                    <input type="hidden" id="productLength" value="<?= $product['base_length'] ?? 110 ?>">
                    <button class="calc-btn" onclick="calculateFabric()"><i class="fas fa-calculator"></i> Рассчитать расход ткани</button>
                    <div class="fabric-result" id="fabricResult">
                        📏 Расход ткани: <span id="fabricAmount">—</span> метров
                    </div>
                </div>
                
                <div class="quantity-selector">
                    <button class="quantity-btn" onclick="changeQuantity(-1)">−</button>
                    <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="99">
                    <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                </div>
                
                <form action="add-to-cart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
                    <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                    <input type="hidden" name="quantity" id="formQuantity" value="1">
                    <input type="hidden" name="size" id="formSize" value="46">
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
        
        document.getElementById('quantity').addEventListener('change', function() {
            let val = parseInt(this.value);
            if (isNaN(val) || val < 1) val = 1;
            if (val > 99) val = 99;
            this.value = val;
            document.getElementById('formQuantity').value = val;
        });
        
        // ВЫБОР РАЗМЕРА
        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                const sizeValue = this.getAttribute('data-size');
                document.getElementById('selectedSize').value = sizeValue;
                document.getElementById('formSize').value = sizeValue;
                calculateFabric(); // Пересчитываем ткань при смене размера
            });
        });
        
        // РАСЧЁТ ТКАНИ
        function calculateFabric() {
            const size = parseInt(document.getElementById('selectedSize').value);
            const fabricWidth = parseInt(document.getElementById('fabricWidth').value);
            const fabricType = document.getElementById('fabricType').value;
            let productLength = parseInt(document.getElementById('productLength').value);
            
            if (isNaN(productLength) || productLength < 40) productLength = 110;
            
            // Коэффициент размера
            let sizeCoeff = 1.0;
            if (size >= 56) sizeCoeff = 1.45;
            else if (size >= 52) sizeCoeff = 1.3;
            else if (size >= 48) sizeCoeff = 1.15;
            else if (size >= 44) sizeCoeff = 1.03;
            else if (size >= 40) sizeCoeff = 0.92;
            
            // Коэффициент типа ткани
            let fabricCoeff = (fabricType === 'stretch') ? 1.05 : 1.15;
            
            // Базовый расход
            let consumption = (productLength / 100) * sizeCoeff;
            
            // Поправка на ширину
            if (fabricWidth <= 140) {
                consumption = consumption * 1.35;
            } else if (fabricWidth >= 200) {
                consumption = consumption * 0.85;
            }
            
            // Усадка
            consumption = consumption * fabricCoeff;
            
            // Округление
            consumption = Math.ceil(consumption * 10) / 10;
            
            // Запас
            if (consumption < 1.5) consumption += 0.1;
            else if (consumption < 2.5) consumption += 0.15;
            else consumption += 0.2;
            
            consumption = Math.ceil(consumption * 10) / 10;
            
            document.getElementById('fabricAmount').innerHTML = consumption.toFixed(1);
        }
        
        // Авторасчёт при изменении параметров
        document.getElementById('fabricWidth').addEventListener('change', calculateFabric);
        document.getElementById('fabricType').addEventListener('change', calculateFabric);
        
        // Расчёт при загрузке
        window.addEventListener('DOMContentLoaded', calculateFabric);
    </script>
</body>
</html>