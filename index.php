<?php 
// 1. Include the database connection and the shared header
include 'db_connect.php'; 
include 'header.php'; 
?>

<h2>Latest Laptops</h2>
    
    <div class="product-grid">
        <?php
        // 2. Write the SQL Query
        $sql = "SELECT * FROM laptops ORDER BY id DESC";
        
        // 3. Execute the query against the database
        $result = $conn->query($sql);

        // 4. Check if we found any laptops
        if ($result->num_rows > 0) {
            // 5. Loop through each row
            // $row will temporarily hold the data for ONE laptop per iteration
            while($row = $result->fetch_assoc()) {
                /* We drop out of PHP mode (?>) to write clean HTML, then re-enter PHP (<?php)*/
                ?>
                
                <div class="product-card">
                    <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         onerror="this.src='https://via.placeholder.com/250x150?text=No+Image'">
                    
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    
                    <p class="brand">Brand: <?php echo htmlspecialchars($row['brand']); ?></p>
                    
                    <p class="price">Ksh<?php echo number_format($row['price'], 2); ?></p>
                    
                    <a href="product.php?id=<?php echo $row['id']; ?>" class="btn" >View Details</a>
                </div>

                <?php
            }
        } else {
            echo "<p>No laptops found in the database.</p>";
        }
        ?>
    </div>

</div> 

</body>
</html>