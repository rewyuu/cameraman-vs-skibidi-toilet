<?php
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

    if (isset($_POST['add_product'])) {
        $name = sanitizeInput($_POST['name']);
        $price = sanitizeInput($_POST['price']);
        $category = sanitizeInput($_POST['category']);
        $image = $_FILES['image'];

        $target_dir = "images/";
        $unique_filename = uniqid() . '_' . time() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
        $target_file = $target_dir . $unique_filename;

        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            $sql = "INSERT INTO products (name, price, image, category) VALUES ('$name', '$price', '$unique_filename', '$category')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['message'] = "Product added successfully.";
            } else {
                $_SESSION['message'] = "Error: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['message'] = "Sorry, there was an error uploading your file.";
        }
        header("Location: admin.php?page=products");
        exit();
    }

    if (isset($_POST['edit_product'])) {
        $product_id = sanitizeInput($_POST['product_id']);
        $name = sanitizeInput($_POST['name']);
        $price = sanitizeInput($_POST['price']);
        $category = sanitizeInput($_POST['category']);
        $image = $_FILES['edit_image'];

        if ($image['name']) {
            $target_dir = "images/";
            $unique_filename = uniqid() . '_' . time() . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            $target_file = $target_dir . $unique_filename;
            move_uploaded_file($image['tmp_name'], $target_file);
            $image_sql = ", image = '$unique_filename'";
        } else {
            $image_sql = '';
        }

        $sql = "UPDATE products SET name = '$name', price = '$price', category = '$category'$image_sql WHERE id = '$product_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Product updated successfully.";
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($conn);
        }
        header("Location: admin.php?page=products");
        exit();
    }

    if (isset($_POST['delete_product'])) {
        $product_id = sanitizeInput($_POST['product_id']);
        $sql = "DELETE FROM products WHERE id = '$product_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Product deleted successfully.";
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($conn);
        }
        header("Location: admin.php?page=products");
        exit();
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="path/to/bootstrap.min.css">
    <title>Admin Products</title>
</head>
<body>
    <h2>Manage Products</h2>

    <?php if ($message): ?>
        <div class='alert alert-success'><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="admin.php?page=products" method="post" enctype="multipart/form-data" class="mb-4">
        <div class="form-group">
            <input type="text" name="name" class="form-control" placeholder="Product Name" required>
        </div>
        <div class="form-group">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
        </div>
        <div class="form-group">
            <input type="file" name="image" class="form-control-file" required>
        </div>
        <div class="form-group">
            <select name="category" class="form-control" required>
                <option value="" disabled selected>Select product type</option>
                <option value="Dishes">Dishes</option>
                <option value="Add-ons">Add-ons</option>
                <option value="Drinks">Drinks</option>
            </select>
        </div>
        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
    </form>

    <h2>Products List</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Image</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM products";
            $products = mysqli_query($conn, $sql);
            while ($product = mysqli_fetch_assoc($products)) {
            ?>
            <tr>
                <td><?php echo $product['name']; ?></td>
                <td><?php echo $product['price']; ?></td>
                <td><img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-thumbnail" width="50"></td>
                <td><?php echo $product['category']; ?></td>
                <td>
                    <form action="admin.php?page=products" method="post" enctype="multipart/form-data" class="d-inline-block">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="text" name="name" class="form-control mb-2" placeholder="New Name">
                        <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="New Price">
                        <input type="file" name="edit_image" class="form-control-file mb-2">
                        <select name="category" class="form-control mb-2">
                            <option value="" disabled selected>Select product type</option>
                            <option value="Dishes">Dishes</option>
                            <option value="Add-ons">Add-ons</option>
                            <option value="Drinks">Drinks</option>
                        </select>
                        <button type="submit" name="edit_product" class="btn btn-warning">Update</button>
                    </form>
                    <form action="admin.php?page=products" method="post" class="d-inline-block">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="delete_product" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <script src="path/to/bootstrap.min.js"></script>
</body>
</html>
