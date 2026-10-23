
<?php
session_start();
require 'db.php';

$error = '';
$username = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, password FROM users WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;

            $stmt->close();
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | My Blog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e5f4f1, #f3f6fa);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #263238;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 36px;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.09);
        }

        .logo {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: #087e74;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            font-weight: bold;
            margin: 0 auto 20px;
        }

        h1 {
            text-align: center;
            margin: 0 0 10px;
            color: #075e56;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d4dde4;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 20px;
            outline: none;
        }

        input:focus {
            border-color: #087e74;
            box-shadow: 0 0 0 3px rgba(8, 126, 116, 0.12);
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #087e74;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #06665e;
        }

        .error {
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #ffd0d0;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .register {
            text-align: center;
            margin: 24px 0 0;
            font-size: 14px;
            color: #64748b;
        }

        a {
            color: #087e74;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 26px 22px;
            }
        }
    </style>
</head>

<body>

    <main class="login-card">

        <div class="logo">B</div>

        <h1>Welcome Back!</h1>

        <p class="subtitle">
            Login to continue to your blog dashboard.
        </p>

        <?php if ($error !== ''): ?>
            <div class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter your username"
                value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Login to Account</button>

        </form>

        <p class="register">
            Don't have an account?
            <a href="register.php">Create an account</a>
        </p>

        <div class="back-home">
            <a href="index.php">← Back to Blog</a>
        </div>

    </main>

</body>
</html>
