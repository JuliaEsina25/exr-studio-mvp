<?php
session_start();

$product = [
    'id' => 8,
    'name' => 'Брюки прямые классические',
    'price' => 5300,
    'description' => 'Классические прямые брюки из костюмной ткани. Идеальная посадка, стрелки.',
    'images' => ['trousers-classic.jpeg'],
    'base_length' => 105
];

include 'product-template.php';
?>
