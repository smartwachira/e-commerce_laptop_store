<?php
include 'db_connect.php';
include 'header.php'; //this starts the session automatically

// ---PART 1: HANDLE ADDING TO CART ---
if (isset($_POST["add_to_cart"])){
    //get data from the form in product.php
    $product_id = intval($_POST['laptop_id']);
    $quantity_requested = intval($_POST['quantity']);

    // Check database for actual stock availability
    $stmt = $conn->prepare("SELECT stock_quantity FROM laptops WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $current_stock_in_db = $row['stock_quantity'];
    //check if a cart already exists, if not, create an empty array
    if (!isset($_SESSION['cart'])){
        $_SESSION['cart'] = [];
    }

    // Calculate total quantity (Existing in cart + New request)
    $current_qty_in_cart = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
    $total_wanted = $current_qty_in_cart + $quantity_requested;

    // The strict check
    if ($total_wanted > $current_stock_in_db){

        $allowed_to_add = $current_stock_in_db - $current_qty_in_cart;

        echo "<script>alert('Error: You already have $current_qty_in_cart in cart. We only have $current_stock_in_db in stock. You can only add $allowed_to_add more.');</script>";
        echo "<script>window.history.back();</script>";
        exit;

    } else {
        // Stock is sufficient, proceed strictly
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity_requested;
        } else {
            $_SESSION['cart'][$product_id] = $quantity_requested;
        }
        echo "<div class='container' style='color: green; margin-top:10px;'>Item added to cart successfully!</div>";
    }

    
    
}

// --part 2: handle removing from cart
if (isset($_GET['action']) && $_GET['action'] == 'remove'){
    $id_to_remove = intval($_GET['id']);
    unset($_SESSION['cart'][$id_to_remove]);
}


?>

<div class="container">
    <h2>Your Shopping Cart</h2>

    <?php
    // Check if cart is empty
    if (empty($_SESSION['cart'])){
        echo "<p>Your cart is empty. <a href='index.php'>Go Shopping</a></p>";
    } else{
        ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="border-bottom: 20px solid #ddd; text-align: left;">
                    <th style="padding: 10px;">Product</th>
                    <th style="padding: 10px;">Price</th>
                    <th style="padding: 10px;">Quantity</th>
                    <th style="padding: 10px;">Total</th>
                    <th style="padding: 10px;">Action</th>

                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;

                //Loop through every item in the SESSION cart
                //$ id is the Laptop ID, $qty is how many they want
                foreach ($_SESSION['cart'] as $id => $qty){

                    //fetch the actual product details from DB based on ID
                    $sql = "SELECT * FROM laptops WHERE id = $id";
                    $result = $conn->query($sql);

                    if($result -> num_rows > 0){
                        $product = $result -> fetch_assoc();

                        $line_total = $product["price"] * $qty;
                        $grand_total += $line_total;
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;">
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <br>
                                <small><?php echo htmlspecialchars($product['brand']); ?></small>
                            </td>
                            <td style="padding: 10px;"><?php echo number_format($product["price"], 2);?></td>
                            <td style="padding: 10px;"><?php echo $qty; ?></td>
                            <td style="padding: 10px;"><?php echo number_format($line_total, 2); ?></td>
                            <td style="padding: 10px;">
                                <a href="cart.php?action=remove&id=<?php echo $id; ?>"
                                style="color:red; text-decoration:none;"
                                >Remove</a>
                            </td>

                        </tr>
                        <?php
                    }

                }
                ?>
                
            </tbody>
        </table>
        <div style="text-align: right; margin-top:20px;">
            <h3>Total Amount: Ksh<?php echo number_format($grand_total, 2); ?></h3>

            <a href="index.php" class="btn" style="background-color: #95a5a6;">Continue Shopping</a>
            <a href="checkout.php" class="btn" style="background-color: #27ae60;">Proceed to Checkout</a>
        </div>
        <?php
    }    
    ?>
</div>
</body>
</html>