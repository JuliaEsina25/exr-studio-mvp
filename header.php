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
