<?php
session_start();
include('database.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $reference_number = $_POST['reference_number'];

    $userID = $_SESSION['user']['id'];

    $query = "INSERT INTO gcash_info (user_id, name, number, reference_number) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error: " . $conn->error); 
    }

    $stmt->bind_param("isss", $userID, $name, $number, $reference_number);
    $stmt->execute();


    if ($stmt->affected_rows > 0) {
        header('Location: order.php');
        exit;
    } else {
        echo "Error: Failed to process payment.";
    }

    $stmt->close();
}

$conn->close();
?>
