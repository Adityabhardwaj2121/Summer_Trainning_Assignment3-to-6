<?php
session_start();
include "config.php";

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 3) {
    $error = "Too many failed attempts. Try again later.";
} elseif (isset($_POST['login'])) {
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];

    $login_id = htmlspecialchars($login_id);

    $stmt = $con->prepare("SELECT * FROM users WHERE email=? OR phone=?");
    $stmt->bind_param("ss", $login_id, $login_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['login_attempts'] = 0;

            header("Location: dashboard.php");
            exit;
        }
    }

    $_SESSION['login_attempts'] += 1;
    $error = "Invalid credentials. Attempt: " . $_SESSION['login_attempts'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background-color: #000000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .container {
            background-color: #1c1c1c;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-4px);
        }

        h2 {
            text-align: center;
            color: #ffffff;
            margin-bottom: 2rem;
            font-size: 1.75rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #333333;
            border-radius: 6px;
            font-size: 1rem;
            background-color: #ffffff;
            color: #000000;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #000000;
            opacity: 0.7;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #ffffff;
            box-shadow: 0 0 6px rgba(255, 255, 255, 0.2);
        }

        button {
            width: 100%;
            padding: 0.875rem;
            background: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover {
            background: #333333;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-link p {
            color: #ffffff;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #cccccc;
            text-decoration: underline;
        }

        .error {
            color: #ffffff;
            background-color: rgba(255, 0, 0, 0.5);
            padding: 0.875rem;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            border: 1px solid #ffffff;
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <input type="text" name="login_id" placeholder="Email or Phone Number" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="register-link">
            <p>Don't have an account? <a href="register.html">Register here</a></p>
        </div>
    </div>
</body>
</html>