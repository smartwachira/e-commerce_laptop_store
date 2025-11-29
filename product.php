<?php
include 'db_connect.php';
include 'header.php';

// 1. check if an ID exists in the URL
if (isset($_GET['id'])){
    $id = $_GET['id'];

    //2. prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM laptops WHERE id = ?");

    //3. bind the parameters
    $stmt->bind_param('i',$id);

    //4. Execute-> and get results

    $stmt->execute();
    $result = $stmt->get_result();

    // Check if product actually exists
    if ($result->num_rows > 0){
        $product = $result->fetch_assoc();

    } else{
        echo "<div class='container'><h2>Product not found!</h2></div>";
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>

<div class="product-detail" style="display: flex; gap: 40px; background:white; border-radius: 8px;">

    <div style="flex: 1;">
        <img src="images/<?php echo htmlspecialchars($product['image_url']); ?>"
        alt="<?php echo htmlspecialchars($product['image_url']); ?>"
        style="max-width: 100%; border-radius: 8px;"
        onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
    </div>

    <div style="flex: 1;">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <h3 style="color: #7f8c8d;"><?php echo htmlspecialchars($product["brand"]); ?></h3>
        <h2 style="color: #e74c3c"><?php echo number_format($product["price"])?></h2>

        <p style="line-height: 1.6"><?php echo nl2br(htmlspecialchars($product["description"])); ?></p>

        <p><strong>Stock:</strong> <?php echo $product["stock_quantity"]; ?> unit available</p>
        <form action="cart.php" method="POST" style="margin-top: 20px;">
            <input type="hidden" name="laptop_id" value="<?php echo $product["id"]; ?>">
            <input type="hidden" name="laptop_price" value="<?php echo $product['price']; ?>">

            <label for="quantity">Quantity:</label>
            <input id="quantity" type="number" name="quantity" value="1" min="1" max="<php echo $product['stock_quantity']; ?>" style="padding: 5px; width: 60px; color:black;">

            <button type="submit" name="add_to_cart" class="btn" style="border: none; cursor: pointer; margin-left:10px;">Add to Cart</button>

        </form>

    </div>
    

</div>
<script>
    const qtyInput = document.getElementById('quantity');

    const maxStock = <?php echo $product['stock_quantity']; ?>;

    qtyInput.addEventListener('input', function(){
        let currentValue = parseInt(this.value);

        if (currentValue > maxStock){
            alert("Sorry, we only have " + maxStock + " units in stock.");
            this.value = maxStock;
        }

        if (currentValue < 1){
            this.value = 1;
        }
    });
</script>
</body>
</html>