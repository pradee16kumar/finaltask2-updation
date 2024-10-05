<?php
 $conn = mysqli_connect("localhost", "root", "", "task");

if (!$conn) {
     die("Connection failed: " . mysqli_connect_error());
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = $_POST['customer'];
    $products = $_POST['product'];
    $quantities = $_POST['quantity'];

    
    $customerReportCheckSql = "SELECT * FROM customer_report WHERE customer_id = '$customerId'";
    $customerReportCheckResult = mysqli_query($conn, $customerReportCheckSql);

    if (mysqli_num_rows($customerReportCheckResult) == 0) {
        $insertCustomerReportSql = "INSERT INTO customer_report (customer_id, order_count_a, order_count_b, order_count_c, order_count_d, order_count_e) 
                                    VALUES ('$customerId', 0, 0, 0, 0, 0)";
        mysqli_query($conn, $insertCustomerReportSql);
    }

    $insertOrderSql = "INSERT INTO orders (customer_id) VALUES ('$customerId')";
    mysqli_query($conn, $insertOrderSql);
    $orderId = mysqli_insert_id($conn);  

    foreach ($products as $index => $product) {
        $quantity = $quantities[$index];

       
        $productCheckSql = "SELECT product_id, quantity FROM inventory WHERE product_name = '$product'";
        $productCheckResult = mysqli_query($conn, $productCheckSql);
        $inventoryRow = mysqli_fetch_assoc($productCheckResult);
        $product_id = $inventoryRow['product_id'];
        $inventory = $inventoryRow['quantity'];

        if ($inventory >= $quantity) {
          
            $newQuantity = $inventory - $quantity;
            $updateInventorySql = "UPDATE inventory SET quantity = '$newQuantity' WHERE product_id = '$product_id'";
            mysqli_query($conn, $updateInventorySql);

            $insertLineSql = "INSERT INTO order_lines (customer_id, product_id, quantity, backordered) 
                              VALUES ('$customerId', '$product_id', '$quantity', 0)";
            mysqli_query($conn, $insertLineSql);
            $backorder = 0;
        } else {
            
            $backorder = $quantity - $inventory;
            $updateInventorySql = "UPDATE inventory SET quantity = 0 WHERE product_id = '$product_id'";
            mysqli_query($conn, $updateInventorySql);

            $insertLineSql = "INSERT INTO order_lines (customer_id, product_id, quantity, backordered) 
                              VALUES ('$customerId', '$product_id', '$inventory', '$backorder')";
            mysqli_query($conn, $insertLineSql);
        }

       
        if ($backorder > 0) {
            $updateOrderReportSql = "UPDATE order_report 
                                     SET total_order = total_order + '$quantity', total_backorder = total_backorder + '$backorder' 
                                     WHERE product_id = '$product_id'";
        } else {
            $updateOrderReportSql = "UPDATE order_report 
                                     SET total_order = total_order + '$quantity' WHERE product_id = '$product_id'";
        }
        mysqli_query($conn, $updateOrderReportSql);

        
        $orderCountColumn = 'order_count_' . strtolower($product);
        $updateCustomerReportSql = "UPDATE customer_report 
                                    SET $orderCountColumn = $orderCountColumn + 1 
                                    WHERE customer_id = '$customerId'";
        mysqli_query($conn, $updateCustomerReportSql);
    }

    echo "Orders processed successfully.";
    mysqli_close($conn);
} else {
    echo "Invalid request method.";
}
?>
