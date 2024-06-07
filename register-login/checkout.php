<?php
session_start();
include('database.php');

$userID = $_SESSION['user']['id'];

$query = "SELECT full_name, address, email, phone, is_senior_or_pwd FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user['full_name'];
$userAddress = $user['address'];
$userEmail = $user['email'];
$userPhone = $user['phone'];
$isSeniorOrPwd = isset($_GET['senior_pwd']) ? 1 : $user['is_senior_or_pwd'];
$stmt->close();

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

$totalPrice = 0;
$orderedItems = [];

foreach ($cartItems as $item) {
    $totalPrice += ($item['item_price'] * $item['quantity']);
    $orderedItems[] = [
        'item_name' => $item['item_name'],
        'item_price' => $item['item_price'],
        'quantity' => $item['quantity']
    ];
}

$vatRate = 0.12; 
$seniorDiscountRate = 0.20; 

$vatAmount = $totalPrice * $vatRate;
$totalPriceWithVat = $totalPrice + $vatAmount;

if ($isSeniorOrPwd) {
    $discountAmount = $totalPriceWithVat * $seniorDiscountRate;
    $totalPriceWithVat -= $discountAmount;
} else {
    $discountAmount = 0;
}

function updateCartCount($conn, $userID, $cartCount) {
    $query = "UPDATE users SET cart_count = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $cartCount, $userID);
    $stmt->execute();
    $stmt->close();
}

$totalPriceWithVatFormatted = number_format($totalPriceWithVat, 2);


    if (isset($_POST['place_order'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $payment_method = $_POST['payment_method'];
    
        if ($payment_method == 'Gcash') {
            $_SESSION['gcash_order'] = [
                'user_id' => $userID,
                'address' => $userAddress,
                'payment_method' => $payment_method,
                'ordered_items' => $orderedItems,
                'total_price' => $totalPriceWithVat
            ];
    
            header('Location: gcashPayment.php?total_price=' . urlencode($totalPriceWithVatFormatted));
            exit;
        }

    if (isset($_POST['use_different_number'])) {
        $phone = $_POST['new_number'];
    } else {
        $phone = $userPhone;
    }

    if ($_POST['address_option'] === 'new') {
        $address = $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['zipcode'] . ', ' . $_POST['region'];
    } else {
        $address = $userAddress;
    }

    if ($phone !== $userPhone) {
        $update_phone_query = "UPDATE users SET phone = ? WHERE id = ?";
        $stmt = $conn->prepare($update_phone_query);
        $stmt->bind_param("si", $phone, $userID);
        $stmt->execute();
        $stmt->close();
    }

    if ($name !== $userName) {
        $update_name_query = "UPDATE users SET full_name = ? WHERE id = ?";
        $stmt = $conn->prepare($update_name_query);
        $stmt->bind_param("si", $name, $userID);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['is_senior_or_pwd'])) {
        $isSeniorOrPwd = 1;
        $discountAmount = $totalPriceWithVat * $seniorDiscountRate;
        $totalPriceWithVat -= $discountAmount;
        $update_senior_query = "UPDATE users SET is_senior_or_pwd = ? WHERE id = ?";
        $stmt = $conn->prepare($update_senior_query);
        $stmt->bind_param("ii", $isSeniorOrPwd, $userID);
        $stmt->execute();
        $stmt->close();
    }

    $insert_order_query = "INSERT INTO orders (user_id, address, payment_type, ordered_items, total_price) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_order_query);
    $stmt->bind_param("isssd", $userID, $address, $payment_method, json_encode($orderedItems), $totalPriceWithVat);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    $clear_cart_query = "DELETE FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($clear_cart_query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->close();

    $cartCount = 0;
    updateCartCount($conn, $userID, $cartCount);

    $_SESSION['cart_count'] = 0;

    header('Location: order.php?order_id=' . $order_id);
    exit;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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

        .order-summary {
            margin-bottom: 30px;
        }

        .order-summary table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-summary th, .order-summary td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .checkout-form {
            background-color: #f2f2f2;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .inputBox {
            margin-bottom: 15px;
        }

        .inputBox input, .inputBox select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .inputBox select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #fff;
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg fill="%23333" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 12l-6-6H4l6 6 6-6h-2l-6 6z"/></svg>');
            background-size: 12px;
            background-repeat: no-repeat;
            background-position-x: calc(100% - 10px);
            background-position-y: center;
            padding-right: 30px;
        }

        .btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
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

        .side-by-side {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .side-by-side .btn {
            flex-shrink: 0;
            margin-left: 10px;
        }

        /* The switch - the box around the slider */
        .switch {
        font-size: 17px;
        position: relative;
        display: inline-block;
        width: 3.5em;
        height: 2em;
        }

        /* Hide default HTML checkbox */
        .switch input {
        opacity: 0;
        width: 0;
        height: 0;
        }

        /* The slider */
        .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #B0B0B0;
        border: 1px solid #B0B0B0;
        transition: .4s;
        border-radius: 32px;
        outline: none;
        }

        .slider:before {
        position: absolute;
        content: "";
        height: 2rem;
        width: 2rem;
        border-radius: 50%;
        outline: 2px solid #B0B0B0;
        left: -1px;
        bottom: -1px;
        background-color: #fff;
        transition: transform .25s ease-in-out 0s;
        }

        .slider-icon {
        opacity: 0;
        height: 12px;
        width: 12px;
        stroke-width: 8;
        position: absolute;
        z-index: 999;
        stroke: #222222;
        right: 60%;
        top: 30%;
        transition: right ease-in-out .3s, opacity ease-in-out .15s;
        }

        input:checked + .slider {
        background-color: #222222;
        }

        input:checked + .slider .slider-icon {
        opacity: 1;
        right: 20%;
        }

        input:checked + .slider:before {
        transform: translateX(1.5em);
        outline-color: #181818;
        }
    </style>
    <script>
        function toggleSeniorDiscount() {
            const checkbox = document.getElementById('senior-checkbox');
            const isChecked = checkbox.checked;
            if (isChecked) {
                window.location.href = window.location.pathname + '?senior_pwd=1';
            } else {
                window.location.href = window.location.pathname;
            }
        }
    </script>
</head>
<body>

<div class="container">
    <div class="home-button">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
    </div>
    <h1 class="heading">Checkout</h1>

    <div class="order-summary">
        <h2>Order Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo 'P' . number_format($item['item_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo 'P' . number_format($item['item_price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3">Total Price</td>
                    <td><?php echo 'P' . number_format($totalPrice, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3">VAT (12%)</td>
                    <td><?php echo 'P' . number_format($vatAmount, 2); ?></td>
                </tr>
                <?php if ($isSeniorOrPwd): ?>
                    <tr>
                        <td colspan="3">Senior/PWD Discount (20%)</td>
                        <td>-<?php echo 'P' . number_format($discountAmount, 2); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3"><strong>Grand Total</strong></td>
                    <td><strong><?php echo 'P' . number_format($totalPriceWithVat, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
            <label class="switch">
                <input type="checkbox" id="senior-checkbox" name="is_senior_or_pwd" value="1" <?php echo $isSeniorOrPwd ? 'checked' : ''; ?> onclick="toggleSeniorDiscount()">
                <span class="slider">
                <svg class="slider-icon" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="presentation"><path fill="none" d="m4 16.5 8 8 16-16"></path></svg> 
                </span>
                
        </label>
        <br>
        <br>
        <div>I am a Senior Citizen / PWD</div>
    </div>

    <div class="checkout-form">
        <h2>Complete Your Order</h2>
        <form action="" method="POST">
            <div class="inputBox">
                <input type="text" name="name" value="<?php echo htmlspecialchars($userName); ?>" placeholder="Your Name" required>
            </div>
            <div class="inputBox">
                <input type="tel" name="number" value="<?php echo htmlspecialchars($userPhone); ?>" placeholder="Your Phone Number" readonly>
            </div>
            <div class="inputBox">
                <input type="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" placeholder="Your Email" required>
            </div>
            <div class="inputBox">
                <select name="payment_method" required> 
                    <option value="">Select Payment Method</option>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                    <option value="Gcash">Gcash</option>
                </select>
            </div>
            <div class="inputBox">
                <input type="text" name="address" value="<?php echo htmlspecialchars($userAddress); ?>" placeholder="Address" readonly>
            </div>
            <div class="side-by-side">
                <input type="submit" name="place_order" value="Place Order" class="btn">
            </div>
        </form>
    </div>
</div>

</body>
</html>