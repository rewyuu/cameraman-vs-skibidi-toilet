<?php
$totalPrice = isset($_GET['total_price']) ? $_GET['total_price'] : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gcash Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .heading {
            color: #333;
        }

        .gcash-details {
            margin-top: 20px;
        }

        img {
            width: 400px;
            height: 350px;
            margin: 0 auto;
            display: block;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"] {
            width: calc(100% - 24px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
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
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-right: 120px;
        }

        .btn.red {
            background-color: #dc3545;
        }

        .btn.red:hover {
            background-color: red;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
    <script>
        function validateForm() {
            const number = document.getElementById('number').value;
            const errorElement = document.getElementById('number-error');
            const regex = /^\d{11}$/;
            if (!regex.test(number)) {
                errorElement.textContent = "Please enter a valid 11-digit phone number.";
                return false;
            }
            errorElement.textContent = "";
            return true;
        }
    </script>
</head>
<body>


<div class="container">
    <h1 class="heading">Gcash Payment</h1>
    <div class="gcash-details">
        <img src="./images/gcash.JPG" alt="gcash">
    </div>
    <h2>Total Amount to be Paid: P<?php echo htmlspecialchars($totalPrice); ?></h2>
    <form action="process_gcash_payment.php" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <input type="text" id="name" name="name" placeholder="Enter your name" required>
        </div>
        <div class="form-group">
            <input type="text" id="number" name="number" placeholder="Enter your number" required>
            <div id="number-error" class="error"></div>
        </div>
        <div class="form-group">
            <input type="text" id="reference_number" name="reference_number" placeholder="Enter reference number" required>
        </div>
        <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($totalPrice); ?>">
        <a href="checkout.php" class="btn red">Return to Checkout</a>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

</body>
</html>
