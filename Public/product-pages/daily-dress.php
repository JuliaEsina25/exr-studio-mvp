<?php
session_start();

$product = [
    'id' => 3,
    'name' => 'Повседневное платье',
    'price' => 3990,
    'description' => 'Удобное платье из натурального хлопка. Для повседневной носки. Дышащая ткань, идеально для лета.',
    'images' => ['daily-dress.jpeg'],
    'base_length' => 95
];

include 'product-template.php';
?>
