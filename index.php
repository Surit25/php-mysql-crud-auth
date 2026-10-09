<?php
session_start();
require 'db.php';

// Check user login session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blog Posts</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <a href="logout.php">Logout</a> | <a href="create.php">Create New Post</a>
    <hr>
    <h3>Blog Posts</h3>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
            <small>Posted on: <?php echo $row['created_at']; ?></small><br>
            <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> | 
            <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this post?');">Delete</a>
        </div>
        <hr>
    <?php endwhile; ?>
</body>
</html>