<?php
include "config.php"; 
session_start();

function validatePassword($password) {
    return preg_match("/^(?=.*[A-Z])(?=.*\d).{8,}$/", $password);
}

if (isset($_POST['register'])) {
    $name  = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (!$email || !$name || !$phone || !$password) {
        $error = "All fields are required.";
    } elseif (!validatePassword($password)) {
        $error = "Password must be at least 8 characters, contain a capital letter and a number.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $con->prepare("INSERT INTO users(name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

        if ($stmt->execute()) {
            $success = "Registration successful!";
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            background-color: #000000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            background-color: #1c1c1c;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #333333;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="text"]::placeholder,
        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #000000;
            opacity: 0.7;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #ffffff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
            outline: none;
        }

        button {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            border-radius: 4px;
            padding: 10px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #333333;
        }

        .login-link {
            color: #ffffff;
            text-align: center;
            margin-top: 15px;
            display: block;
        }

        .login-link a {
            color: #ffffff;
            text-decoration: none;
        }

        .login-link a:hover {
            color: #cccccc;
        }

        .error {
            color: #ffffff;
            background-color: rgba(255, 0, 0, 0.5);
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ffffff;
        }

        .success {
            color: #ffffff;
            background-color: rgba(0, 128, 0, 0.5);
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form action="register.php" method="post" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" name="register">Register</button>
            <div class="login-link">
                Already have an account? <a href="login.html">Log in</a>
            </div>
        </form>
    </div>
</body>
</html>