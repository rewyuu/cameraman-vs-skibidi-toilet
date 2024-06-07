<?php
session_start();
include('database.php');

if (!isset($_SESSION["user"])) {
    header('Location: order.php');
    exit;
}

$userID = $_SESSION['user']['id'];

function updateCartCount($conn, $userID) {
    $query = "UPDATE users SET cart_count = (SELECT COUNT(*) FROM cart_items WHERE user_id = ?) WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userID, $userID);
    $stmt->execute();
    $stmt->close();

    $query = "SELECT COUNT(*) AS total_items FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalItems = $row['total_items'];
    $stmt->close();

    if ($totalItems === 0) {
        $query = "UPDATE users SET cart_count = 0 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->close();
    }
}

function isItemInCart($conn, $userID, $itemName) {
    $query = "SELECT COUNT(*) AS count FROM cart_items WHERE user_id = ? AND item_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userID, $itemName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
    $stmt->close();
    return $count > 0;
}

function incrementCartItemQuantity($conn, $userID, $itemName) {
    $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND item_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userID, $itemName);
    $stmt->execute();
    $stmt->close();
}

function decrementCartItemQuantity($conn, $userID, $itemName) {
    $query = "UPDATE cart_items SET quantity = GREATEST(quantity - 1, 0) WHERE user_id = ? AND item_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userID, $itemName);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_quantity'], $_POST['update_quantity_id'])) {
        $update_value = $_POST['update_quantity'];
        $update_id = $_POST['update_quantity_id'];
        $update_quantity_query = "UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_quantity_query);
        $stmt->bind_param("iii", $update_value, $update_id, $userID);
        if ($stmt->execute()) {
            updateCartCount($conn, $userID);
            header('Location: cart.php');
            exit;
        }
    }

    if (isset($_POST['item_name'], $_POST['item_price'], $_POST['quantity'])) {
        $itemName = $_POST['item_name'];
        $itemPrice = $_POST['item_price'];
        $quantity = $_POST['quantity'];

        if (isItemInCart($conn, $userID, $itemName)) {
            echo "<script>alert('Item is already in the cart');</script>";
        } else {
            $insertQuery = "INSERT INTO cart_items (user_id, item_name, item_price, quantity) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("isdi", $userID, $itemName, $itemPrice, $quantity);
            $stmt->execute();
            $stmt->close();
        }

        updateCartCount($conn, $userID);
        header('Location: cart.php');
        exit;
    }
}

if (isset($_GET['increment']) && isset($_GET['item'])) {
    incrementCartItemQuantity($conn, $userID, $_GET['item']);
    updateCartCount($conn, $userID);
    header('Location: cart.php');
    exit;
}

if (isset($_GET['decrement']) && isset($_GET['item'])) {
    decrementCartItemQuantity($conn, $userID, $_GET['item']);
    updateCartCount($conn, $userID);
    header('Location: cart.php');
    exit;
}

if (isset($_GET['remove'])) {
    $removeID = $_GET['remove'];
    $query = "DELETE FROM cart_items WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $removeID, $userID);
    if ($stmt->execute()) {
        updateCartCount($conn, $userID);
        header('Location: cart.php');
        exit;
    }
}

if (isset($_GET['delete_all'])) {
    $query = "DELETE FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userID);
    if ($stmt->execute()) {
        updateCartCount($conn, $userID);
        header('Location: cart.php');
        exit;
    }
}

$query = "SELECT * FROM cart_items WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        .quantity-form {
            display: flex;
            align-items: center;
        }

        .quantity-input {
            width: 50px;
            margin-right: 10px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .update-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .update-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            color: #dc3545;
            text-decoration: none;
            cursor: pointer;
        }

        .delete-btn:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .btn:hover {
            background-color: #23903c;
        }

        .checkout-btn {
            text-align: right;
            margin-top: 20px;
        }

        .home-btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration:     none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .home-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="index.php" class="home-btn"><i class="fas fa-home"></i> Home</a>
        <h1 class="heading">Shopping Cart</h1>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item) : ?>
                    <tr>
                        <td><?php echo $item['item_name']; ?></td>
                        <td>P<?php echo number_format($item['item_price'], 2); ?></td>
                        <td>
                            <form class="quantity-form" method="post">
                                <input class="quantity-input" type="number" name="update_quantity" min="1" value="<?php echo $item['quantity']; ?>">
                                <input type="hidden" name="update_quantity_id" value="<?php echo $item['id']; ?>">
                                <input class="update-btn" type="submit" value="Update" name="update_update_btn">
                            </form>
                        </td>
                        <td>P<?php echo number_format($item['item_price'] * $item['quantity'], 2); ?></td>
                        <td><a href="cart.php?remove=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Remove item from cart?')"><i class="fas fa-trash"></i> Remove</a></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3">Total Payment</td>
                    <td>P<?php
                        $grandTotal = array_reduce($cartItems, function ($carry, $item) {
                            return $carry + ($item['item_price'] * $item['quantity']);
                        }, 0);
                        echo number_format($grandTotal, 2);
                        ?></td>
                    <td><a href="cart.php?delete_all" class="delete-btn" onclick="return confirm('Are you sure you want to delete all items?')"><i class="fas fa-trash"></i> Delete All</a></td>
                </tr>
            </tbody>
        </table>
        <div class="checkout-btn">
            <a href="checkout.php" class="btn <?php echo ($grandTotal > 0) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
        </div>
    </div>
</body>
</html>
