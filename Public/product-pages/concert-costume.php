<?php
session_start();

$product = [
    'id' => 2,
    'name' => 'Концертный костюм',
    'price' => 8990,
    'description' => 'Яркий сценический костюм для выступлений. Стразы и пайетки создают эффектный образ на сцене.',
    'images' => ['concert-costume1.jpeg'],
    'base_length' => 115
];

include 'product-template.php';
?>
