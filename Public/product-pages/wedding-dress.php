<?php
session_start();

$product = [
    'id' => 7,
    'name' => 'Свадебное платье',
    'price' => 15990,
    'description' => 'Нежное свадебное платье ручной работы. Кружево, шёлк, воздушная фата.',
    'images' => ['wedding-dress.jpeg', 'wedding-dress1.jpeg', 'wedding-dress2.jpeg',],
    'base_length' => 155
];

include 'product-template.php';
?>
