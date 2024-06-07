<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_order'])) {

        $order_id = sanitizeInput($_POST['order_id']);
        $status = sanitizeInput($_POST['status']);

        $sql = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
        if (mysqli_query($conn, $sql)) {
            $message = "Order status updated successfully.";
        } 
        else {
            $message = "Error updating order status: " . mysqli_error($conn);
        }
            header("Location: admin.php?page=orders");
            exit;
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$pending_orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                                      FROM orders 
                                      JOIN users ON orders.user_id = users.id 
                                      WHERE status = 'Pending'");

$accepted_orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                                       FROM orders 
                                       JOIN users ON orders.user_id = users.id 
                                       WHERE status = 'Order Accepted'");

$declined_orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                                       FROM orders 
                                       JOIN users ON orders.user_id = users.id 
                                       WHERE status = 'Order Declined'");

$delivered_orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                                        FROM orders 
                                        JOIN users ON orders.user_id = users.id 
                                        WHERE status = 'Delivered'");

$on_delivery_orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                                          FROM orders 
                                          JOIN users ON orders.user_id = users.id 
                                          WHERE status = 'On Delivery'");

$cancelled_orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                                        FROM orders 
                                        JOIN users ON orders.user_id = users.id 
                                        WHERE status = 'Cancelled'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Manage Orders</h2>

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

    <!-- Pending Orders -->
    <h3 id="pending">Pending Orders</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User Phone</th>
                <th>Status</th>
                <th>Ordered Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($pending_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td>
                    <form action="admin.php?page=orders" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Order Accepted" <?php if ($order['status'] == 'Order Accepted') echo 'selected'; ?>>Order Accepted</option>
                            <option value="Order Declined" <?php if ($order['status'] == 'Order Declined') echo 'selected'; ?>>Order Declined</option>
                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="On Delivery" <?php if ($order['status'] == 'On Delivery') echo 'selected'; ?>>On Delivery</option>
                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_order" class="btn btn-warning mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                        $ordered_items = json_decode($order['ordered_items'], true);
                        foreach ($ordered_items as $item) {
                            echo "{$item['item_name']} - {$item['quantity']} <br>";
                        }
                    ?>
                </td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Accepted Orders -->
    <h3 id="accepted">Accepted Orders</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User Phone</th>
                <th>Status</th>
                <th>Ordered Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($accepted_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td>
                    <form action="admin.php?page=orders" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Order Accepted" <?php if ($order['status'] == 'Order Accepted') echo 'selected'; ?>>Order Accepted</option>
                            <option value="Order Declined" <?php if ($order['status'] == 'Order Declined') echo 'selected'; ?>>Order Declined</option>
                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="On Delivery" <?php if ($order['status'] == 'On Delivery') echo 'selected'; ?>>On Delivery</option>
                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_order" class="btn btn-warning mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                        $ordered_items = json_decode($order['ordered_items'], true);
                        foreach ($ordered_items as $item) {
                            echo "{$item['item_name']} - {$item['quantity']} <br>";
                        }
                    ?>
                </td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Declined Orders -->
    <h3 id="declined">Declined Orders</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User Phone</th>
                <th>Status</th>
                <th>Ordered Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($declined_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td>
                    <form action="admin.php?page=orders" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Order Accepted" <?php if ($order['status'] == 'Order Accepted') echo 'selected'; ?>>Order Accepted</option>
                            <option value="Order Declined" <?php if ($order['status'] == 'Order Declined') echo 'selected'; ?>>Order Declined</option>
                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="On Delivery" <?php if ($order['status'] == 'On Delivery') echo 'selected'; ?>>On Delivery</option>
                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_order" class="btn btn-warning mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                        $ordered_items = json_decode($order['ordered_items'], true);
                        foreach ($ordered_items as $item) {
                            echo "{$item['item_name']} - {$item['quantity']} <br>";
                        }
                    ?>
                </td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- On Delivery Orders -->
    <h3 id="on_delivery">On Delivery</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User Phone</th>
                <th>Status</th>
                <th>Ordered Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($on_delivery_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td>
                    <form action="admin.php?page=orders" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Order Accepted" <?php if ($order['status'] == 'Order Accepted') echo 'selected'; ?>>Order Accepted</option>
                            <option value="Order Declined" <?php if ($order['status'] == 'Order Declined') echo 'selected'; ?>>Order Declined</option>
                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="On Delivery" <?php if ($order['status'] == 'On Delivery') echo 'selected'; ?>>On Delivery</option>
                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_order" class="btn btn-warning mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                        $ordered_items = json_decode($order['ordered_items'], true);
                        foreach ($ordered_items as $item) {
                            echo "{$item['item_name']} - {$item['quantity']} <br>";
                        }
                    ?>
                </td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Delivered Orders -->
    <h3 id="delivered">Delivered Orders</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User Phone</th>
                <th>Status</th>
                <th>Ordered Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($delivered_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td>
                    <form action="admin.php?page=orders" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Order Accepted" <?php if ($order['status'] == 'Order Accepted') echo 'selected'; ?>>Order Accepted</option>
                            <option value="Order Declined" <?php if ($order['status'] == 'Order Declined') echo 'selected'; ?>>Order Declined</option>
                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="On Delivery" <?php if ($order['status'] == 'On Delivery') echo 'selected'; ?>>On Delivery</option>
                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_order" class="btn btn-warning mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                        $ordered_items = json_decode($order['ordered_items'], true);
                        foreach ($ordered_items as $item) {
                            echo "{$item['item_name']} - {$item['quantity']} <br>";
                        }
                    ?>
                </td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Cancelled Orders -->
    <h3 id="cancelled">Cancelled Orders</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User Phone</th>
                <th>Status</th>
                <th>Ordered Items</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($cancelled_orders)): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td>
                    <form action="admin.php?page=orders" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="form-control">
                            <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Order Accepted" <?php if ($order['status'] == 'Order Accepted') echo 'selected'; ?>>Order Accepted</option>
                            <option value="Order Declined" <?php if ($order['status'] == 'Order Declined') echo 'selected'; ?>>Order Declined</option>
                            <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            <option value="On Delivery" <?php if ($order['status'] == 'On Delivery') echo 'selected'; ?>>On Delivery</option>
                            <option value="Cancelled" <?php if ($order['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_order" class="btn btn-warning mt-2">Update</button>
                    </form>
                </td>
                <td>
                    <?php
                        $ordered_items = json_decode($order['ordered_items'], true);
                        foreach ($ordered_items as $item) {
                            echo "{$item['item_name']} - {$item['quantity']} <br>";
                        }
                    ?>
                </td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>