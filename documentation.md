# Task 2: Basic CRUD Application with User Authentication

**Internship Domain:** Web Development in PHP & MySQL

**Organization:** ApexPlanet Software Pvt Ltd

**Developer:** Surit Dey

## 1. Database Schema Documentation

### Database Overview

* **Database Name:** `blog`

* **DBMS:** MySQL

### Table Structure

#### 1. Table Name: `users`

Stores user registration and authentication details. Passwords are securely hashed using PHP's `password_hash()` algorithm.

| Column Name | Data Type | Constraints | Description | 
| ----- | ----- | ----- | ----- | 
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Unique identifier for each user | 
| `username` | `VARCHAR(50)` | `NOT NULL`, `UNIQUE` | Unique username for login | 
| `password` | `VARCHAR(255)` | `NOT NULL` | Hashed user password | 

```
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

```

#### 2. Table Name: `posts`

Stores the blog content created by users, including titles, full content body, and automatic creation timestamps.

| Column Name | Data Type | Constraints | Description | 
| ----- | ----- | ----- | ----- | 
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Unique post identifier | 
| `title` | `VARCHAR(255)` | `NOT NULL` | Title of the blog post | 
| `content` | `TEXT` | `NOT NULL` | Body/Content of the post | 
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Date and time when created | 

```
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

```

## 2. Source Code Architecture & Walkthrough

### Directory Structure

```
blog/
│── db.php               # Database connection using MySQLi
│── schema.sql           # Database schema initialization script
│── register.php         # User registration form & logic (Password Hashing)
│── login.php            # User authentication & session initialization
│── logout.php           # Destroys active session
│── index.php            # Dashboard / Read Posts operation
│── create.php           # Add new post operation
│── edit.php             # Update existing post operation
└── delete.php           # Remove post operation

```

### PHP Modules Codebase

#### `db.php` — Database Connection

```
<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$dbname = "blog";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

```

#### `register.php` — User Registration

```
<?php
session_start();
require 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $message = "Username already taken or registration failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
    <h2>Register</h2>
    <?php if($message) echo "<p style='color:red;'>$message</p>"; ?>
    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>

```

#### `login.php` — User Authentication

```
<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
    <h2>Login</h2>
    <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>
</html>

```

#### `index.php` — Read Operations & Session Protection

```
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head><title>Blog Dashboard</title></head>
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

```

#### `create.php` — Create Post Operation

```
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Create Post</title></head>
<body>
    <h2>Add New Post</h2>
    <form method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>
        <label>Content:</label><br>
        <textarea name="content" rows="5" cols="40" required></textarea><br><br>
        <button type="submit">Publish</button>
    </form>
    <br>
    <a href="index.php">Back to Dashboard</a>
</body>
</html>

```

#### `edit.php` — Update Post Operation

```
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $id);
    
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    }
}

$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head><title>Edit Post</title></head>
<body>
    <h2>Edit Post</h2>
    <form method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>
        <label>Content:</label><br>
        <textarea name="content" rows="5" cols="40" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>
        <button type="submit">Update Post</button>
    </form>
    <br>
    <a href="index.php">Cancel</a>
</body>
</html>

```

#### `delete.php` — Delete Post Operation

```
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit();
?>

```

#### `logout.php` — Session Termination

```
<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>

```

## 3. Features & Security Implementation

1. **Password Hashing:** Passwords are encoded using `password_hash()` (BCRYPT) and verified with `password_verify()`.

2. **SQL Injection Prevention:** Prepared statements (`$conn->prepare`) with bound parameters are utilized for all SQL queries.

3. **XSS Protection:** Output variables are sanitized using `htmlspecialchars()` before rendering on web pages.

4. **Session Control:** Sensitive CRUD routes verify `$_SESSION['user_id']` and redirect unauthorized traffic to `login.php`.