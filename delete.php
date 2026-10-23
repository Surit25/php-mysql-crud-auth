
<?php
session_start();
require 'db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate post ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit();
}

$error = '';

// Delete only after the confirmation form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['confirm_delete'] ?? '') === 'yes') {

        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?deleted=1");
            exit();
        } else {
            $error = "Unable to delete the post. Please try again.";
            $stmt->close();
        }

    } else {
        header("Location: index.php");
        exit();
    }
}

// Check whether the post exists
$stmt = $conn->prepare("SELECT title FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Delete Post | My Blog</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f7f6;
            color: #24332f;
            min-height: 100vh;
        }

        .navbar {
            background: #087f6e;
            color: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .brand {
            font-size: 23px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        .container {
            width: 90%;
            max-width: 550px;
            margin: 75px auto;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.06);
        }

        .warning-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: #fff0f0;
            color: #c62828;
            font-size: 30px;
        }

        h1 {
            font-size: 26px;
            margin-bottom: 14px;
        }

        .description {
            color: #74817d;
            line-height: 1.7;
            font-size: 15px;
            margin-bottom: 22px;
        }

        .post-title {
            background: #f3f7f6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            overflow-wrap: anywhere;
            font-weight: bold;
        }

        .error {
            background: #fff0f0;
            color: #b42318;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 13px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-cancel {
            background: #edf2f0;
            color: #34443f;
        }

        .btn-cancel:hover {
            background: #dfe8e4;
        }

        .btn-delete {
            background: #c62828;
            color: white;
        }

        .btn-delete:hover {
            background: #a61f1f;
        }

        .note {
            margin-top: 22px;
            font-size: 12px;
            color: #84918c;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 16px 5%;
            }

            .container {
                margin: 35px auto;
            }

            .card {
                padding: 26px 18px;
            }

            h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="brand">✍ My Blog</div>

    <nav class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="create.php">Create Post</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">
    <section class="card">

        <div class="warning-icon">!</div>

        <h1>Delete This Post?</h1>

        <p class="description">
            Are you sure you want to delete this post?
            This action cannot be undone.
        </p>

        <div class="post-title">
            <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="delete.php?id=<?php echo (int)$id; ?>">

            <div class="actions">
                <a href="index.php" class="btn btn-cancel">
                    Cancel
                </a>

                <button
                    type="submit"
                    name="confirm_delete"
                    value="yes"
                    class="btn btn-delete"
                >
                    Yes, Delete Post
                </button>
            </div>

        </form>

        <p class="note">
            Please confirm carefully before deleting.
        </p>

    </section>
</main>

</body>
</html>
