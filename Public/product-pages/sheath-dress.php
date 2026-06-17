<?php
session_start();

$product = [
    'id' => 9,
    'name' => 'Платье-футляр трикотажное',
    'price' => 4200,
    'description' => 'Элегантное платье-футляр из мягкого трикотажа. Для офиса и вечера.',
    'images' => ['dress-sheath.jpeg'],
    'base_length' => 100
];

include 'product-template.php';
?>
