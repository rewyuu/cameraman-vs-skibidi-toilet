<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = sanitizeInput($_POST['user_id']);
        $sql = "DELETE FROM users WHERE id = '$user_id'";
        if (mysqli_query($conn, $sql)) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<h2>Registered Users</h2>

<?php if ($message) echo "<div class='alert alert-success'>$message</div>"; ?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Username</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = mysqli_fetch_assoc($users)) { ?>
        <tr>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['phone']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['address']; ?></td>
            <td>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
