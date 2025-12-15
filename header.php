<?php
// header.php
session_start(); 

//Security: Generate CSRF Token if it doesn't exist
if(empty($_SESSION['csrf_token'])){
    // bin2hex(random_bytes(32)) creates a long, unguessable string
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptop Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav>
        <div class="logo">TechLaptops</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">Cart</a></li>
        </ul>
    </nav>

    <div class="container">