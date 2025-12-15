<?php
include 'db_connect.php';
include 'header.php';

//1. redirect if cart is empty
if (empty($_SESSION['cart'])){
    header("Location: index.php");
    exit;
}

$order_success = false;
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    //Security Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        die("Security Error: Invalid CSRF Token. Request blocked.");
    }

    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);


    //START TRANSACTION
    //This turns off auto-commit. nothing is saved until we say so
    $conn->begin_transaction();

    try{
        //step A: handle user (guest checkout logic)
        //check for email in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0){
            //user exists, get their ID
            $user = $result->fetch_assoc();
            $user_id = $user['id'];

        } else {
            //New user, create them (with a dummy password for now)
            $dummy_pass = password_hash("guest123", PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss",$name,$email,$dummy_pass);
            $stmt->execute();
            $user_id = $conn->insert_id; //Get the ID of the user we just made

        }

        // step B: Calculate Total & Create Order Header

        $total_amount = 0;
        // Recalculate total for security (never trust POST data for prices)
        foreach ($_SESSION['cart'] as $pid => $qty){
            $check_sql = $conn->query("SELECT price FROM laptops WHERE id = $pid");
            $p_data = $check_sql->fetch_assoc();
            $total_amount += ($p_data['price'] * $qty);

        }

        //Insert into 'orders' table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("id", $user_id, $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id; //Get the ID of the new Order
        

        //step C: Insert Order Items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, laptop_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");

        // Prepare the stock update statement
        $stmt_stock = $conn->prepare("UPDATE laptops SET stock_quantity = stock_quantity - ? WHERE id = ?");
        foreach ($_SESSION['cart'] as $pid => $qty){
            //Fetch current price again
            $check_sql = $conn->query("SELECT price FROM laptops WHERE id = $pid");
            $p_data = $check_sql->fetch_assoc();
            $price = $p_data['price'];

            //Bind and Execute for EACH item
            $stmt->bind_param("iiid",$order_id, $pid, $qty, $price);
            $stmt->execute();

            //Deduct from Inventory
            $stmt_stock->bind_param("ii",$qty, $pid);
            $stmt_stock->execute();
        }
        // step D: Success
        $conn->commit(); //save everything permanently

        unset($_SESSION['cart']);
        $order_success = true;



    } catch (Exception $e) {
        // If ANY step failed, undo everything
        $conn->rollback();
        $error_msg = "Order failed: " . $e->getMessage();
    }
}
?>

<div class="container">
    <?php if ($order_success): ?>
        <div style="text-align: center; padding: 50px;">
            <h1 style="color">Order Placed successfully!</h1>
            <p>Thank you, <strong><?php echo htmlspecialchars($name); ?></strong>.</p>
            <p>Your Order ID is <?php echo $order_id; ?></p>
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    <?php else: ?>
        <h2>Checkout</h2>

        <?php if ($error_msg): ?>
            <p><?php echo $error_msg; ?></p>
        <?php endif; ?>
        <div style="display: flex; gap: 40px;">
            <form method="POST" style="flex: 1; background:white; padding: 20px; border-radius: 8px;">
                <input type="hidden"  name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">
                <h3>Contact Information</h3>

                <label>Full Name</label><br>
                <input type="text" name="full_name" required style="width: 100%; padding: 8px; margin-bottom: 10px;  color:black;">

                <label >Email Address</label><br>
                <input type="email" name="email" required style="width: 100%; padding: 8px; margin-bottom: 10px;  color:black;">

                <h3>Payment Details</h3>
                <p><em>(Mock Payment - No Card Required)</em></p>

                <button type="submit" class="btn" style="width: 100%; font-size: 1.1rem;">Place Order</button>
            </form>

            <div style="flex: 1; background: #eee; padding: 20px; border-radius: 8px; height: fit-content;">
                <h3>Order Summary</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php
                    $running_total = 0;
                    foreach ($_SESSION['cart'] as $id => $qty){
                        $sql = "SELECT * FROM laptops WHERE id = $id";
                        $res = $conn->query($sql);
                        $item = $res->fetch_assoc();
                        $sub = $item['price'] * $qty;
                        $running_total += $sub;
                        echo "<li style='border-bottom: 1px solid #ccc; padding: 10px 0;'>";
                        echo "<strong>" . $item['name'] . "</strong> x $qty";
                        echo "<span style='float:right;'>$" . number_format($sub, 2) . "</span>";
                        echo "</li>";
                    }
                    ?>
                </ul>
                <h3 style="text-align: right; border-top: 2px solid #333; padding-top: 10px;">
                    Total: $<?php echo number_format($running_total, 2); ?>
                </h3>

            </div>

        </div>
    <?php endif; ?>


</div>
</body>
</html>