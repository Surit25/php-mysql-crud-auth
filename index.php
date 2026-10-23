
<?php
session_start();
require 'db.php';

// Check user login session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Search keyword
$search = trim($_GET['search'] ?? '');
$like = "%" . $search . "%";

// Pagination settings
$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

// Count matching posts
$countSql = "SELECT COUNT(*) AS total
             FROM posts
             WHERE title LIKE ? OR content LIKE ?";

$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("ss", $like, $like);
$countStmt->execute();

$countResult = $countStmt->get_result();
$totalPosts = (int)$countResult->fetch_assoc()['total'];
$countStmt->close();

// Calculate total pages
$totalPages = max(1, (int)ceil($totalPosts / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// Fetch posts for current page
$sql = "SELECT * FROM posts
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $like, $like, $limit, $offset);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Blog Dashboard</title>

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
            margin-left: 14px;
            font-weight: bold;
        }

        .container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .search-box input {
            flex: 1;
            min-width: 0;
            padding: 13px;
            border: 1px solid #ccd5dc;
            border-radius: 8px;
            font-size: 16px;
        }

        button, .btn {
            background: #087e74;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .clear-btn {
            background: #64748b;
        }

        .post-card {
            background: white;
            padding: 24px;
            margin: 18px 0;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            overflow-wrap: anywhere;
        }

        .post-card h3 {
            color: #087e74;
            margin-top: 0;
        }

        .post-date {
            color: #718096;
            font-size: 13px;
        }

        .post-actions {
            margin-top: 18px;
        }

        .edit-btn {
            background: #2563eb;
        }

        .delete-btn {
            background: #dc3545;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 30px 0;
        }

        .pagination a {
            padding: 10px 14px;
            text-decoration: none;
            border: 1px solid #087e74;
            color: #087e74;
            border-radius: 6px;
        }

        .pagination a.active {
            background: #087e74;
            color: white;
        }

        .pagination a.disabled {
            pointer-events: none;
            opacity: 0.4;
        }

        .empty-message {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            color: #64748b;
        }

        .results-info {
            color: #64748b;
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #64748b;
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
                margin-top: 22px;
                padding: 0 14px;
            }

            .search-box {
                flex-wrap: wrap;
            }

            .search-box input {
                flex-basis: 100%;
            }

            .post-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <h2>My Blog</h2>

    <nav>
        <a href="index.php">Home</a>
        <a href="create.php">+ Create Post</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="container">

    <div class="welcome">
        <h2>
            Welcome,
            <?= htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>!
        </h2>
        <p>Manage your blog posts from one place.</p>
    </div>

    <h2>Blog Posts</h2>

    <!-- Search Form -->
    <form class="search-box" method="GET" action="index.php">
        <input
            type="text"
            name="search"
            placeholder="Search posts by title or content..."
            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
        >

        <button type="submit">Search</button>

        <?php if ($search !== ''): ?>
            <a class="btn clear-btn" href="index.php">Clear</a>
        <?php endif; ?>
    </form>

    <p class="results-info">
        <?php if ($search !== ''): ?>
            Search results for:
            <strong><?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?></strong>
            —
        <?php endif; ?>

        <?= $totalPosts ?> post(s) found.
    </p>

    <!-- Display Posts -->
    <?php if ($result->num_rows > 0): ?>

        <?php while ($row = $result->fetch_assoc()): ?>

            <article class="post-card">

                <h3>
                    <?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>
                </h3>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $row['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) ?>
                </p>

                <div class="post-date">
                    Posted on:
                    <?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="post-actions">
                    <a
                        class="btn edit-btn"
                        href="edit.php?id=<?= (int)$row['id'] ?>"
                    >Edit</a>

                    <a href="delete.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                </div>

            </article>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty-message">
            <h3>No posts found</h3>
            <p>Try another search keyword or create a new post.</p>
            <a class="btn" href="create.php">Create New Post</a>
        </div>

    <?php endif; ?>

    <?php $stmt->close(); ?>

    <!-- Pagination -->
    <?php if ($totalPosts > 0 && $totalPages > 1): ?>

        <div class="pagination">

            <?php
            $previousPage = max(1, $page - 1);
            $nextPage = min($totalPages, $page + 1);

            $previousUrl = '?' . http_build_query([
                'search' => $search,
                'page' => $previousPage
            ]);

            $nextUrl = '?' . http_build_query([
                'search' => $search,
                'page' => $nextPage
            ]);
            ?>

            <a
                href="<?= htmlspecialchars($previousUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="<?= $page <= 1 ? 'disabled' : '' ?>"
            >Previous</a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <?php
                $pageUrl = '?' . http_build_query([
                    'search' => $search,
                    'page' => $i
                ]);
                ?>

                <a
                    href="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="<?= $page === $i ? 'active' : '' ?>"
                ><?= $i ?></a>

            <?php endfor; ?>

            <a
                href="<?= htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="<?= $page >= $totalPages ? 'disabled' : '' ?>"
            >Next</a>

        </div>

    <?php endif; ?>

</main>

<footer>
    &copy; <?= date('Y') ?> My Blog Dashboard
</footer>

</body>
</html>
