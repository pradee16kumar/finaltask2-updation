<?php

$conn = mysqli_connect("localhost", "root", "", "task");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$customerQuery = "SELECT customer_id, customer_name FROM customers";
$customersResult = mysqli_query($conn, $customerQuery);


$productQuery = "SELECT product_id, product_name FROM inventory";
$productsResult = mysqli_query($conn, $productQuery);

$productCountQuery = "SELECT COUNT(*) AS total_products FROM inventory";
$productCountResult = mysqli_query($conn, $productCountQuery);
$productCountRow = mysqli_fetch_assoc($productCountResult);
$maxProducts = $productCountRow['total_products'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form</title>
</head>
<body>

    <h2>Order Form</h2>
    <form method="post" action="process_order.php" onsubmit="checkDuplicateProducts(event)">
        <label for="customer">Select Customer:</label>
        <select name="customer" id="customer" required>
        <option value="">--Select a Customer--</option> 
            <?php while ($row = mysqli_fetch_assoc($customersResult)): ?>
                <option value="<?php echo $row['customer_id']; ?>">
                    <?php echo $row['customer_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <h3>Products:</h3>
        <div id="product-container">
            <div class="product-item">
                <label for="product">Select Product:</label>
                <select name="product[]" required>
                <option value="">--Select a Product--</option> 
                    <?php
                    mysqli_data_seek($productsResult, 0); 
                    while ($row = mysqli_fetch_assoc($productsResult)): ?>
                        <option value="<?php echo $row['product_name']; ?>">
                            <?php echo $row['product_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity[]" required min="1">
                <br><br>
            </div>
        </div>
        <button type="button" id="add-product-btn" onclick="addProduct()">Add More Products</button>
        <input type="submit" value="Submit Order">
    </form>

    <script>
        let productCount = 1; 
        const maxProducts = <?php echo $maxProducts; ?>; 

        
        function addProduct() {
            if (productCount >= maxProducts) {
                alert("You cannot add more products. Maximum limit reached.");
                return;
            }

            const container = document.getElementById('product-container');
            const productItem = document.createElement('div');
            productItem.className = 'product-item';
            productItem.innerHTML = `
                <label for="product">Select Product:</label>
                <select name="product[]" required>
                <option value="">--Select a Product--</option> 
                    <?php
                    mysqli_data_seek($productsResult, 0); 
                    while ($row = mysqli_fetch_assoc($productsResult)) {
                        echo '<option value="' . $row['product_name'] . '">' . $row['product_name'] . '</option>';
                    }
                    ?>
                </select>
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity[]" required min="1">
                <button type="button" onclick="removeProduct(this)">Remove</button>
                <br><br>
            `;
            container.appendChild(productItem);
            productCount++;
        }

      
        function removeProduct(button) {
            const productItem = button.parentElement;
            productItem.remove();
            productCount--;
        }

        function checkDuplicateProducts(event) {
            const products = document.getElementsByName('product[]');
            const productSet = new Set();
            
            for (let i = 0; i < products.length; i++) {
                const productValue = products[i].value;
                
                if (productSet.has(productValue) && productValue !== "") {
                    alert('Duplicate product selected: ' + productValue + '. Please select different products.');
                    event.preventDefault(); 
                    return false;
                }
                
                productSet.add(productValue); 
            }

            
            return true;
        }
    </script>
</body>
</html>

<?php

mysqli_close($conn);
?>
