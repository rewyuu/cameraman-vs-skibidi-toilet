<?php
require_once "database.php";
session_start();

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $errors = array();

    if (empty($username) || empty($password)) {
        $errors[] = "All fields are required";
    }

    if (count($errors) > 0) {
        $_SESSION["errors"] = $errors;
        header("Location: login.php");
        exit();
    }
     else {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("SQL statement failed: " . mysqli_stmt_error($stmt));
        } 
        else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user) {
                if ($password === $user["password"]) {
                    if ($username === 'admin') { 
                        $_SESSION["admin"] = true;
                        header("Location: ../admin-panel-2/admin.php");
                        exit();
                    } else {
                        $_SESSION["user"] = $user;
                        header("Location: index.php");
                        exit();
                    }
                } else {
                    $errors[] = "Invalid username or password";
                }
            } else {
                $errors[] = "User not found";
            }
        }

        $_SESSION["errors"] = $errors;
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-top: 100px;
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
            padding: 25px;
        }

        .alert-dismissible .btn-close {
            position: absolute;
            top: -10px;
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

        .btn-login {
            margin-left: 40px;
        }

        .btn-hover:hover, .btn-login:hover {
            background-color: #ff4500;
            transform: scale(1.05);
            color: white;
        }

        #jerry-logo {
            display:inline-block;
            margin-right: 100px;
            width: 25%;
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
?>

    <div class="container">
        <form action="login.php" method="post">
            <div class="container-title">
                <p>
                <img id="jerry-logo" src="images/jerrys-logos.png" alt=""> LOGIN
                </p>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                <input type="checkbox" id="showPasswordCheckbox">
                <label for="showPasswordCheckbox">Show Password</label>
        </div>
            <div class="form-btn">
                <input type="submit" class="btn-hover" value="Login" name="login">
            </div>
        </form>
        <div class="registered">
            <p>Not Registered? <a href="registration.php" class="btn-login">Click me to Register</a></p>
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
