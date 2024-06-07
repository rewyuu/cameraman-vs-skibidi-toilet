<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: ../register-login/login.php");
    exit();
}
include('../register-login/database.php');

$message = '';

function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../register-login/login.php");
    exit();
}

$products = mysqli_query($conn, "SELECT * FROM products");
$orders = mysqli_query($conn, "SELECT orders.*, users.phone 
                               FROM orders 
                               JOIN users ON orders.user_id = users.id");
$users = mysqli_query($conn, "SELECT * FROM users");

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
<div class="admin-panel d-flex">
    <div class="left-panel bg-dark text-white p-3">
        <h1>Admin Panel</h1>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="admin.php" method="post">
            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
        </form>
        <nav class="mt-4">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="admin.php?page=home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="admin.php?page=products">Manage Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="admin.php?page=orders">Manage Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="admin.php?page=users">Registered Users</a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="middle-section flex-grow-1 p-4">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            include($page . ".php");
        } else {
            include('home.php');
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
