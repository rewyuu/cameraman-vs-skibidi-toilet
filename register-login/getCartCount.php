<?php
session_start();
include('database.php');

if (!isset($_SESSION["user"])) {
    echo json_encode(['cart_count' => 0]);
    exit;
}

$userID = $_SESSION['user']['id'];
$query = "SELECT cart_count FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['cart_count' => $row['cart_count']]);

$stmt->close();
$conn->close();
?>
