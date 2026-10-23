
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$title = '';
$content = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = "Please fill in both the title and content.";
    } elseif (strlen($title) > 255) {
        $error = "Title must not exceed 255 characters.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO posts (title, content) VALUES (?, ?)"
        );

        $stmt->bind_param("ss", $title, $content);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?created=1");
            exit();
        } else {
            $error = "Unable to publish your post. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Post | My Blog</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fa;
            color: #263238;
        }

        .navbar {
            background: #087e74;
            color: white;
            padding: 20px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .navbar h2 {
            margin: 0;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 16px;
            font-weight: bold;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .heading {
            margin-bottom: 28px;
        }

        .heading h1 {
            margin: 0 0 10px;
            color: #075e56;
            font-size: 28px;
        }

        .heading p {
            color: #718096;
            line-height: 1.6;
            margin: 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 9px;
        }

        input, textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #ccd5dc;
            border-radius: 8px;
            font-family: inherit;
            font-size: 15px;
            margin-bottom: 22px;
            outline: none;
        }

        input:focus, textarea:focus {
            border-color: #087e74;
            box-shadow: 0 0 0 3px rgba(8, 126, 116, 0.12);
        }

        textarea {
            min-height: 220px;
            resize: vertical;
            line-height: 1.6;
        }

        .help-text {
            color: #718096;
            font-size: 13px;
            margin-top: -14px;
            margin-bottom: 22px;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 5px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 13px 20px;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .publish-btn {
            background: #087e74;
            color: white;
        }

        .publish-btn:hover {
            background: #06665e;
        }

        .cancel-btn {
            background: #edf1f5;
            color: #334155;
        }

        .error {
            padding: 13px;
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #ffd0d0;
            border-radius: 8px;
            margin-bottom: 22px;
        }

        footer {
            text-align: center;
            color: #718096;
            padding: 25px;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 18px;
            }

            .navbar a {
                margin-left: 0;
                margin-right: 12px;
            }

            .container {
                margin: 24px auto;
                padding: 0 14px;
            }

            .form-card {
                padding: 23px 18px;
            }

            .heading h1 {
                font-size: 24px;
            }

            .actions .btn {
                text-align: center;
                flex: 1;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <h2>My Blog</h2>

    <nav>
        <a href="index.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <section class="form-card">

        <div class="heading">
            <h1>Create a New Post</h1>
            <p>Share your thoughts, ideas and knowledge with your readers.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="create.php">

            <label for="title">Post Title</label>

            <input
                type="text"
                id="title"
                name="title"
                placeholder="Enter an interesting title"
                maxlength="255"
                value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="content">Post Content</label>

            <textarea
                id="content"
                name="content"
                placeholder="Write your blog post here..."
                required
            ><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>

            <p class="help-text">
                Tip: Use a clear title and write content that is easy to read.
            </p>

            <div class="actions">
                <button type="submit" class="btn publish-btn">
                    Publish Post
                </button>

                <a href="index.php" class="btn cancel-btn">
                    Cancel
                </a>
            </div>

        </form>

    </section>

</main>

<footer>
    &copy; <?= date('Y') ?> My Blog Dashboard
</footer>

</body>
</html>