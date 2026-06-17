<?php
session_start();

$product = [
    'id' => 1,
    'name' => 'Новогоднее платье',
    'price' => 5990,
    'description' => 'Элегантное новогоднее платье с пайетками. Идеально для праздничного вечера.',
    //  имена файлов
    'images' => ['new-year-dress3.jpeg', 'new-year-dress2.jpg', 'new-year-dress1.jpg'],
    'base_length' => 110
];

include 'product-template.php';
?>
