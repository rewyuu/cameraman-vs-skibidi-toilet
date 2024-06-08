<?php
session_start();
include('database.php');

if (!isset($_SESSION['user']['id'])) {
    echo "User is not logged in.";
    exit;
}

$userID = $_SESSION['user']['id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_order'])) {
    $orderID = $_POST['order_id'];

    $query = "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $orderID, $userID);
    $stmt->execute();
    $stmt->close();

    header("Location: order.php");
    exit();
}

$query = "SELECT o.id, o.address as order_address, o.payment_type, o.status, o.ordered_items, o.total_price, o.created_at, u.full_name, o.phone, 
                 gi.name AS gcash_name, gi.number AS gcash_number, gi.reference_number AS gcash_reference_number
          FROM orders o 
          INNER JOIN users u ON o.user_id = u.id
          LEFT JOIN gcash_info gi ON o.id = gi.id
          WHERE o.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $row['ordered_items'] = json_decode($row['ordered_items'], true);
    $orders[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .heading {
            text-align: center;
            color: #333;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-table th, .order-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .order-status {
            margin-top: 30px;
        }

        .order-status .status-heading {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .order-status .order-item {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f2f2f2;
        }

        .order-status .order-item .item-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .order-status .order-item .item-info span {
            font-weight: bold;
        }

        .order-status .order-item .item-info .status {
            font-style: italic;
            color: #007bff;
        }

        .order-status .order-item .item-info .status.pending {
            color: #ffc107;
        }

        .order-status .order-item .item-info .status.cancelled {
            color: #dc3545;
        }

        .order-status .order-item .item-info .status.delivered {
            color: #28a745;
        }

        .home-button {
            display: block;
            width: 100%;
            margin-top: 20px;
        }

        .home-button a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .home-button a:hover {
            background-color: #0056b3;
        }

        .cancel-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .cancel-button:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="heading">Order Status</h1>
        <div class="home-button">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
        </div>

        <div class="order-status">
            <?php foreach ($orders as $order) : ?>
                <div class="order-item">
                    <div class="item-info">
                        <span>Order #</span>
                        <span><?php echo $order['id']; ?></span>
                    </div>
                    <div class="item-info">
                        <span>Full Name:</span>
                        <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                    <div class="item-info">
                        <span>Phone:</span>
                        <span><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    <div class="item-info">
                        <span>Address:</span>
                        <span><?php echo htmlspecialchars($order['order_address']); ?></span>
                    </div>
                    <div class="item-info">
                        <span>Payment Method:</span>
                        <span><?php echo $order['payment_type']; ?></span>
                    </div>
                    <div class="item-info">
                        <span>Status:</span>
                        <span class="status <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span>
                    </div>
                    <div class="item-info">
                        <span>Ordered Items:</span>
                        <ul>
                            <?php foreach ($order['ordered_items'] as $item) : ?>
                                <li><?php echo $item['item_name']; ?> (<?php echo $item['quantity']; ?>) - <?php echo 'P' . number_format($item['item_price'] * $item['quantity'], 2); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="item-info">
                        <span>Total Price:</span>
                        <span><?php echo 'P' . number_format($order['total_price'], 2); ?></span>
                    </div>
                    <?php if ($order['payment_type'] === 'Gcash') : ?>
                        <div class="item-info">
                            <span>Gcash Name:</span>
                            <span><?php echo htmlspecialchars($order['gcash_name']); ?></span>
                        </div>
                        <div class="item-info">
                            <span>Gcash Number:</span>
                            <span><?php echo htmlspecialchars($order['gcash_number']); ?></span>
                        </div>
                        <div class="item-info">
                            <span>Gcash Reference Number:</span>
                            <span><?php echo htmlspecialchars($order['gcash_reference_number']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['status'] === 'Pending') : ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" class="cancel-button" name="cancel_order">Cancel Order</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (empty($orders)) : ?>
                <p class="status-heading">You have no orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
