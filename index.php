<?php
session_start();
require_once __DIR__ . "/config/database.php";

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// Загрузка товаров из БД
$products = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT MIN(id) as id, name, MIN(price) as price, 
                   MIN(description) as description, MIN(image_url) as image_url, 
                   MIN(category) as category 
            FROM products 
            WHERE is_active = 1 
            GROUP BY name 
            ORDER BY id
        ");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = getDemoProducts();
    }
} else {
    $products = getDemoProducts();
}

function getDemoProducts() {
    return [
        ['id' => 1, 'name' => 'Новогоднее платье', 'price' => 5990, 'image_url' => 'new-year-dress3.jpeg', 'description' => 'Элегантное платье для новогоднего вечера.'],   
        ['id' => 3, 'name' => 'Повседневное платье', 'price' => 3990, 'image_url' => 'daily-dress.jpeg', 'description' => 'Удобное платье из натурального хлопка.'],
        ['id' => 7, 'name' => 'Свадебное платье', 'price' => 15990, 'image_url' => 'wedding-dress.jpeg', 'description' => 'Нежное свадебное платье ручной работы.'],
        ['id' => 2, 'name' => 'Концертный костюм', 'price' => 8990, 'image_url' => 'concert-costume1.jpeg', 'description' => 'Яркий сценический костюм для выступлений.'],
        ['id' => 8, 'name' => 'Брюки прямые классические', 'price' => 5300, 'image_url' => 'trousers-classic.jpeg', 'description' => 'Классические прямые брюки из костюмной ткани.'],
        ['id' => 9, 'name' => 'Платье-футляр трикотажное', 'price' => 4200, 'image_url' => 'dress-sheath.jpeg', 'description' => 'Элегантное платье-футляр из мягкого трикотажа.'],
    ];
}

$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>EXR Studio | Мастерская одежды</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/exr-studio-mvp/Public/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="location.href='index.php'">
                    <div class="logo-mixed">
                        <span class="logo-letter logo-e">E</span>
                        <span class="logo-letter logo-r">R</span>
                    </div>
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
                <div class="cart-link">
                    <a href="cart.php">🛒 Корзина <span class="cart-count"><?= $cartCount ?></span></a>
                </div>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">МАСТЕРСКАЯ ПО ИЗГОТОВЛЕНИЮ ОДЕЖДЫ</h1>
                <p class="hero-text">ДОБРО ПОЖАЛОВАТЬ В МАСТЕРСКУЮ, ГДЕ КАЖДАЯ ДЕТАЛЬ ИМЕЕТ ЗНАЧЕНИЕ.</p>
                <div class="hero-buttons">
                    <button onclick="document.getElementById('collections').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">СМОТРЕТЬ КОЛЛЕКЦИИ</button>
                    <button onclick="document.getElementById('contact').scrollIntoView({behavior: 'smooth'})" class="btn btn-secondary">ЗАПИСАТЬСЯ НА ПРИЕМ</button>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section (О мастерской) -->
    <section class="about">
        <div class="container">
            <h2 class="section-title">О МАСТЕРСКОЙ</h2>
            <p class="section-subtitle">ВДОХНОВЕНИЕ И МАСТЕРСТВО В КАЖДОЙ ДЕТАЛИ</p>
            <div class="about-content">
                <div class="about-text">
                    <p>Наша мастерская начала свой путь в <strong>2015 году</strong> с идеи создания уникальной одежды для детских танцевальных коллективов. Мы стремимся предлагать клиентам эксклюзивные коллекции, сочетающие качество, комфорт и индивидуальность.</p>
                    <p>Наши ценности — это <strong>внимание к деталям</strong>, творческий подход и стремление к совершенству в каждой строчке. Команда профессионалов объединяет многолетний опыт и любовь к своему делу.</p>
                    <div class="features">
                        <div class="feature">
                            <div class="feature-icon"><i class="fas fa-ruler-combined"></i></div>
                            <div class="feature-text">
                                <h3>ИНДИВИДУАЛЬНЫЙ ПОДХОД</h3>
                                <p>Каждое изделие создаётся с учётом особенностей фигуры и пожеланий клиента</p>
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon"><i class="fas fa-award"></i></div>
                            <div class="feature-text">
                                <h3>ВЫСОКОЕ КАЧЕСТВО</h3>
                                <p>Используем только качественные материалы и уделяем внимание каждой детали</p>
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon"><i class="fas fa-clock"></i></div>
                            <div class="feature-text">
                                <h3>СОБЛЮДЕНИЕ СРОКОВ</h3>
                                <p>Ценим время клиентов и всегда выполняем заказы в оговоренные сроки</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="/exr-studio-mvp/Public/Pic/Rectangle2.png" alt="Мастерская" onerror="this.src='">
                </div>
            </div>
        </div>
    </section>

    <section id="collections" class="collections">
        <div class="container">
            <h2 class="section-title">НАШИ КОЛЛЕКЦИИ</h2>
            <p class="section-subtitle">Ознакомьтесь с нашими коллекциями и выберите свой идеальный образ</p>
            <div class="collections-grid">
                <?php foreach ($products as $product): ?>
                <div class="collection-card">
                    <?php
                    $pageLink = '';
                    switch($product['name']) {
                        case 'Свадебное платье':
                            $pageLink = '/exr-studio-mvp/Public/product-pages/wedding-dress.php';
                            break;
                        case 'Новогоднее платье':
                            $pageLink = '/exr-studio-mvp/Public/product-pages/new-year-dress.php';
                            break;
                        case 'Концертный костюм':
                            $pageLink = '/exr-studio-mvp/Public/product-pages/concert-costume.php';
                            break;
                        case 'Повседневное платье':
                            $pageLink = '/exr-studio-mvp/Public/product-pages/daily-dress.php';
                            break;
                        case 'Брюки прямые классические':
                            $pageLink = '/exr-studio-mvp/Public/product-pages/trousers-classic.php';
                            break;
                        case 'Платье-футляр трикотажное':
                            $pageLink = '/exr-studio-mvp/Public/product-pages/sheath-dress.php';
                            break;
                        default:
                            $pageLink = '#';
                    }
                    ?>
                    <a href="<?= $pageLink ?>">
                        <div class="collection-img"><img src="/exr-studio-mvp/Public/Pic/<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>"></div>
                        <div class="collection-content">
                            <h3 class="collection-title"><?= $product['name'] ?></h3>
                            <p class="collection-desc"><?= $product['description'] ?></p>
                            <div class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</div>
                        </div>
                    </a>
                    <form action="add-to-cart.php" method="POST" class="add-to-cart-form" style="padding: 0 20px 20px;">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="product_name" value="<?= $product['name'] ?>">
                        <input type="hidden" name="product_price" value="<?= $product['price'] ?>">
                        <input type="hidden" name="product_image" value="<?= $product['image_url'] ?>">
                        <button type="submit" class="btn btn-outline" style="width: 100%;">🛒 В корзину</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section (Записаться на прием) -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2 class="section-title" style="text-align: left;">КОНТАКТЫ</h2>
                    <p>Свяжитесь с нами для обсуждения вашего проекта</p>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div><strong>АДРЕС</strong><br>Саров, ул. Пионерская, д. 9</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div><strong>ТЕЛЕФОН</strong><br>+7 (495) 123-45-67</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div><strong>EMAIL</strong><br>info@exr-studio.ru</div>
                    </div>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-vk"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>ОСТАВИТЬ ЗАЯВКУ</h3>
                    <form action="submit-form.php" method="POST">
                        <div class="form-group"><input type="text" name="name" placeholder="Ваше имя" required></div>
                        <div class="form-group"><input type="tel" name="phone" placeholder="Телефон" required></div>
                        <div class="form-group"><input type="email" name="email" placeholder="Email"></div>
                        <div class="form-group"><textarea name="message" placeholder="Опишите ваш проект" rows="4"></textarea></div>
                        <button type="submit" class="btn btn-primary btn-block">ОТПРАВИТЬ ЗАЯВКУ</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-copy">
                    <p>&copy; 2026 EXR Studio. Все права защищены.</p>
                    <p>Разработано с ❤️</p>
            </div>
        </div>
    </footer>

    <script>
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const formData = new FormData(form);
            await fetch('add-to-cart.php', { method: 'POST', body: formData });
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) cartCount.textContent = (parseInt(cartCount.textContent) || 0) + 1;
            alert('Товар добавлен в корзину!');
        });
    }); 
    </script>
</body>
</html>
