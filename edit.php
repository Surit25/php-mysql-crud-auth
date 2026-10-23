
<?php
session_start();
require 'db.php';

// Check whether user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get and validate post ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit();
}

$error = '';
$title = '';
$content = '';

// Fetch existing post
$stmt = $conn->prepare(
    "SELECT title, content FROM posts WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

// Redirect if post does not exist
if (!$post) {
    header("Location: index.php");
    exit();
}

$title = $post['title'];
$content = $post['content'];

// Update post when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = "Please enter both the title and content.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE posts SET title = ?, content = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $title, $content, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?updated=1");
            exit();
        } else {
            $error = "Unable to update the post. Please try again.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | My Blog</title>

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
            align-items: center;
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
            max-width: 760px;
            margin: 45px auto;
        }

        .back-link {
            display: inline-block;
            color: #087f6e;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .form-card {
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.06);
        }

        .form-card h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #74817d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 9px;
            font-size: 14px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d6e1dd;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: #24332f;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #087f6e;
            box-shadow: 0 0 0 3px rgba(8, 127, 110, 0.10);
        }

        textarea {
            min-height: 220px;
            resize: vertical;
            line-height: 1.7;
        }

        .error {
            background: #fff0f0;
            color: #b42318;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 13px 21px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary {
            background: #087f6e;
            color: white;
        }

        .btn-primary:hover {
            background: #066858;
        }

        .btn-secondary {
            background: #edf2f0;
            color: #34443f;
        }

        .btn-secondary:hover {
            background: #dfe8e4;
        }

        .footer-note {
            margin-top: 22px;
            font-size: 12px;
            color: #84918c;
            text-align: center;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 16px 5%;
            }

            .container {
                margin: 25px auto;
            }

            .form-card {
                padding: 23px 18px;
            }

            .form-card h1 {
                font-size: 24px;
            }

            textarea {
                min-height: 180px;
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
        <a class="back-link" href="index.php">← Back to Dashboard</a>

        <section class="form-card">
            <h1>Edit Your Post</h1>
            <p class="subtitle">
                Make changes to your article below and save your updates.
            </p>

            <?php if ($error !== ''): ?>
                <div class="error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="edit.php?id=<?php echo (int)$id; ?>">

                <div class="form-group">
                    <label for="title">Post Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        maxlength="255"
                        placeholder="Enter your post title"
                        value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="content">Post Content</label>
                    <textarea
                        id="content"
                        name="content"
                        placeholder="Write your post here..."
                        required
                    ><?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </section>

        <p class="footer-note">
            Review your changes before saving your post.
        </p>
    </main>

</body>
</html>
