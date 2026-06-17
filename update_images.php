<?php
$db = require_once __DIR__ . '/config/database.php';

// Обновляем пути к картинкам в БД
$images = [
    1 => '/exr-studio-mvp/Public/Pic/Рисунок 4.jpg',
    2 => '/exr-studio-mvp/Public/Pic/Рисунок 5.jpg',
    3 => '/exr-studio-mvp/Public/Pic/Рисунок 6.jpg',
];

foreach ($images as $id => $path) {
    $stmt = $db->prepare("UPDATE products SET image = ? WHERE id = ?");
    $stmt->execute([$path, $id]);
    echo "Обновлен товар ID $id: $path<br>";
}

echo "<br>✅ Готово!";
?>
