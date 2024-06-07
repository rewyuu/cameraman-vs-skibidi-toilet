<?php
session_start();

$username = "";
$email = "";
$password = "";
$passwordRepeat = "";
$phone = "";
$address = "";

if (isset($_POST["submit"])) {
    require_once "database.php";

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["repeat_password"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];

    $errors = array();

    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat) || empty($phone) || empty($address)) {
        array_push($errors, "All fields are required");
    }
    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 characters long");
    }
    if (!preg_match("/[A-Z]/", $password)) {
        array_push($errors, "Password must contain at least one uppercase letter");
    }
    if (!preg_match("/[0-9]/", $password)) {
        array_push($errors, "Password must contain at least one number");
    }
    if (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
        array_push($errors, "Password must contain at least one special character");
    }
    if ($password !== $passwordRepeat) {
        array_push($errors, "Passwords do not match");
    }
    if ($password !== $passwordRepeat) {
        array_push($errors, "Passwords do not match");
    }

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("SQL statement failed");
    } else {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rowCount = mysqli_num_rows($result);
        if ($rowCount > 0) {
            array_push($errors, "Email already exists!");
        }
    }

    if (count($errors) > 0) {
        $_SESSION["errors"] = $errors;
        header("Location: registration.php");
        exit();
    } else {
        $sql = "INSERT INTO users (username, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("SQL statement failed");
        } else {
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $password, $phone, $address);
            mysqli_stmt_execute($stmt);
            $_SESSION["success"] = "You are registered successfully.";
            header("Location: registration.php");
            exit();
        }   
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 50px;
            padding: 0;
            background: linear-gradient(120deg, white, #ff7f50, #ff4500);
            background-size: cover;
            background-repeat: no-repeat;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-top: 75px;
            font-size: 1.6rem;
            position: relative;
        }

        .container-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 30px;
        }

        .form-group {
            margin-bottom: 20px; 
            font-size: 15px;
            margin-left: 10px;
        }

        .form-btn {
            text-align: center;
            margin-top: 20px; 
        }

        .registered {
            margin-top: 40px;
        }

        .form-control {
            font-weight: 700;
        }

        #showPasswordCheckbox {
            margin-top: 10px;
            margin-left: 10px;
        }

        .go-back-button {
            display: block;
            width: 20%;
            text-align: center;
            margin-top: 20px;
        }

        .alert {
            font-size: 20px;
            padding: 40px;
            position: fixed;
            top: 100px;
            left: 50px;
            z-index: 1000;
            opacity: 0;
            animation: slideIn 0.5s forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-dismissible {
            padding-right: 1rem;
        }

        .alert-dismissible .btn-close {
            position: absolute;
            right: 10px;
            padding: 20px;
        }  

        .btn-hover, .btn-login {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 700;
            color: white;
            background-color: #ff7f50; 
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .btn-hover:hover, .btn-login:hover {
            background-color: #ff4500;
            transform: scale(1.05);
        }

        .btn-login {
            margin-left: 50px;
        }

        .btn-login:hover {
            color: white;
        }

        #jerry-logo {
            display:inline-block;
            margin-right: 100px;
            width: 25%;
        }

        .alert-success {
            padding: 20px; 
        }

        .alert-success .btn-close {
            margin-bottom: 30px;
        }

        .alert-success {
            padding: 30px;
        }

        .alert-success .btn-close {
            padding: 12px;
            position: absolute;
            right: 10px;
            top: 5px; 
        }

        .alert-success .btn-close:focus {
            outline: none;
            box-shadow: none;
        }

        .alert-success .btn-close {
            z-index: 1001;
        }
    </style>
</head>
<body>

<?php
if (isset($_SESSION["errors"]) && !empty($_SESSION["errors"])) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
    foreach ($_SESSION["errors"] as $error) {
        echo "$error<br>";
    }
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
    unset($_SESSION["errors"]);
}
if (isset($_SESSION["success"])) {
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>{$_SESSION["success"]}";
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
    unset($_SESSION["success"]);
}
?> 
    
    <div class="container">
        <form action="registration.php" method="post">
            <div class="container-title">
                <p>
                <img id="jerry-logo" src="images/jerrys-logos.png" alt=""> SIGN UP
                </p>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                <input type="checkbox" id="showPasswordCheckbox">
                <label for="showPasswordCheckbox">Show Password</label>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($phone); ?>">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="address" placeholder="Address" value="<?php echo htmlspecialchars($address); ?>">
            </div>
            <div class="form-btn">
                <input id="registerButton" type="submit" class="btn-hover" value="Register" name="submit">
            </div>
        </form>
        <div class="registered">
            <p>Already Registered? <a href="login.php" class="btn-login">Click me to Login</a></p>
        </div>
        <div class="go-back-button">
            <button class="btn-hover" onclick="redirectHome()">Home</button>
        </div>
    </div>
    <script>
        function redirectHome() {
            window.location.href = "../index.php";
        }

        const passwordInput = document.getElementById('password');
        const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');

        showPasswordCheckbox.addEventListener('change', function() {
            if (showPasswordCheckbox.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
