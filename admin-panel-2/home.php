<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../register-login/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_order'])) {
        $order_id = sanitizeInput($_POST['order_id']);
        $status = sanitizeInput($_POST['status']);

        $sql = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Order status updated successfully.";
        } else {
            $_SESSION['message'] = "Error updating order status: " . mysqli_error($conn);
        }
        header("Location: admin.php?page=orders");
        exit;
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

function fetchOrders($status) {
    global $conn;
    $sql = "SELECT orders.*, users.phone 
            FROM orders 
            JOIN users ON orders.user_id = users.id 
            WHERE status = '$status'";
    return mysqli_query($conn, $sql);
}

function fetchGcashInfo($order_id) {
    global $conn;
    $sql = "SELECT * FROM gcash_info WHERE id = '$order_id'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

function fetchMonthlySales() {
    global $conn;
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_price) AS total_sales 
            FROM orders 
            WHERE status = 'Delivered'
            GROUP BY month 
            ORDER BY month DESC";
    return mysqli_query($conn, $sql);
}

function fetchMostPickedFood() {
    global $conn;
    $sql = "SELECT item_name, SUM(quantity) AS total_quantity
            FROM (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(ordered_items, CONCAT('$[', idx, '].item_name'))) AS item_name,
                       JSON_UNQUOTE(JSON_EXTRACT(ordered_items, CONCAT('$[', idx, '].quantity'))) AS quantity
                FROM orders
                JOIN (
                    SELECT 0 AS idx UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                ) AS idxs
                WHERE idx < JSON_LENGTH(ordered_items)
            ) AS items
            GROUP BY item_name
            ORDER BY total_quantity DESC
            LIMIT 5";
    
    return mysqli_query($conn, $sql);
}

$pending_orders = fetchOrders('Pending');
$accepted_orders = fetchOrders('Order Accepted');
$declined_orders = fetchOrders('Order Declined');
$delivered_orders = fetchOrders('Delivered');
$on_delivery_orders = fetchOrders('On Delivery');
$cancelled_orders = fetchOrders('Cancelled');

$monthly_sales = fetchMonthlySales();
$most_picked_food = fetchMostPickedFood();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Welcome to the Admin Panel</h2>
    <p>Use the sidebar to navigate through different sections of the admin panel.</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="#pending" class="btn btn-primary mr-2">Pending Orders</a>
        <a href="#accepted" class="btn btn-primary mr-2">Accepted Orders</a>
        <a href="#declined" class="btn btn-primary mr-2">Declined Orders</a>
        <a href="#delivered" class="btn btn-primary mr-2">Delivered Orders</a>
        <a href="#on_delivery" class="btn btn-primary mr-2">On Delivery Orders</a>
        <a href="#cancelled" class="btn btn-primary">Cancelled Orders</a>
    </div>

    <br>
    <h3>Reports</h3>
    <br>
    <br>
    <div>
        <h4>Monthly Sales</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($monthly_sales)): ?>
                    <tr>
                        <td><?php echo $row['month']; ?></td>
                        <td><?php echo number_format($row['total_sales'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div>
        <h4>Most Picked Food Items</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Food Item</th>
                    <th>Total Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($most_picked_food)): ?>
                    <tr>
                        <td><?php echo $row['item_name']; ?></td>
                        <td><?php echo $row['total_quantity']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php 
    function renderOrdersTable($orders, $title) {
        echo "<h3 id='" . strtolower(str_replace(' ', '_', $title)) . "'>$title</h3>";
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User Phone</th>
                        <th>Status</th>
                        <th>Ordered Items</th>
                        <th>Address</th>
                        <th>Payment Type</th>
                        <th>Total Price</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>";
            
        while ($order = mysqli_fetch_assoc($orders)) {
            echo "<tr>
                    <td>{$order['id']}</td>
                    <td>{$order['phone']}</td>
                    <td>
                        <form action='admin.php?page=orders' method='post'>
                            <input type='hidden' name='order_id' value='{$order['id']}'>
                            <select name='status' class='form-control'>
                                <option value='Pending' " . ($order['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                <option value='Order Accepted' " . ($order['status'] == 'Order Accepted' ? 'selected' : '') . ">Order Accepted</option>
                                <option value='Order Declined' " . ($order['status'] == 'Order Declined' ? 'selected' : '') . ">Order Declined</option>
                                <option value='Delivered' " . ($order['status'] == 'Delivered' ? 'selected' : '') . ">Delivered</option>
                                <option value='On Delivery' " . ($order['status'] == 'On Delivery' ? 'selected' : '') . ">On Delivery</option>
                                <option value='Cancelled' " . ($order['status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                            </select>
                            <button type='submit' name='update_order' class='btn btn-warning mt-2'>Update</button>
                        </form>
                    </td>
                    <td>";
            $ordered_items = json_decode($order['ordered_items'], true);
            foreach ($ordered_items as $item) {
                echo "{$item['item_name']} - {$item['quantity']} <br>";
            }
            echo "</td>
                    <td>{$order['address']}</td>
                    <td>{$order['payment_type']}";
            
            if ($order['payment_type'] == 'Gcash') {
                $gcash_info = fetchGcashInfo($order['id']);
                if ($gcash_info) {
                    echo "<br><b>Name:</b> " . htmlspecialchars($gcash_info['name']) . "<br>" .
                         "<b>Number:</b> " . htmlspecialchars($gcash_info['number']) . "<br>" .
                         "<b>Reference Number:</b> " . htmlspecialchars($gcash_info['reference_number']);
                } else {
                    echo "<br>No GCash info found";
                }
            }
            
            echo "</td>
                    <td>{$order['total_price']}</td>
                    <td>{$order['created_at']}</td>
                </tr>";
        }
        echo "</tbody></table>";
    }

    renderOrdersTable($pending_orders, 'Pending Orders');
    ?>
</div>
</body>
</html>
