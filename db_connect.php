<?php
// db_connect.php

// 1. Define Database Credentials
// In a real production app, these should be in an environment variable (.env file)
// for security, but for this class, variables are fine.
$servername = "localhost";
$username   = "root";      // Default XAMPP/WAMP username
$password   = "";          // Default XAMPP/WAMP password is empty
$dbname     = "ecommerce_db";

// 2. Create Connection using the mysqli Object
// New instance of the mysqli class
$conn = new mysqli($servername, $username, $password, $dbname);

// 3. Check Connection
// -> is the Object Operator (like . in Java or C#) used to access properties
if ($conn->connect_error) {
    // If connection fails, kill the script and show why
    die("Connection failed: " . $conn->connect_error);
}

// TEMPORARY: We will remove this line later.
// This is just to confirm to YOU that it works right now.
echo "Database connected successfully!";
?>