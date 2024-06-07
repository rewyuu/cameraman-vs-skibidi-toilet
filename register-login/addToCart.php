<?php
session_start();
include('database.php');

if (!isset($_SESSION["user"])) {
    http_response_code(403);
    die("Unauthorized access");
}

$userID = $_SESSION['user']['id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['itemName'], $input['itemPrice'])) {
    http_response_code(400);
    die("Invalid request. Please provide itemName and itemPrice.");
}

$itemName = $input['itemName'];
$itemPrice = floatval($input['itemPrice']);

$checkQuery = "SELECT id, quantity FROM cart_items WHERE user_id = ? AND item_name = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("is", $userID, $itemName);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $existingItem = $checkResult->fetch_assoc();
    $itemId = $existingItem['id'];
    $newQuantity = $existingItem['quantity'] + 1;

    $updateQuery = "UPDATE cart_items SET quantity = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $newQuantity, $itemId);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    $insertQuery = "INSERT INTO cart_items (user_id, item_name, item_price, quantity) VALUES (?, ?, ?, 1)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("isd", $userID, $itemName, $itemPrice);
    $insertStmt->execute();
    $insertStmt->close();
}

$updateCartCountQuery = "UPDATE users SET cart_count = cart_count + 1 WHERE id = ?";
$cartStmt = $conn->prepare($updateCartCountQuery);
$cartStmt->bind_param("i", $userID);
$cartStmt->execute();
$cartStmt->close();

echo json_encode(['success' => true]);

$conn->close();
?>
