<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user's role
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Check if the username is set in the session
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';  // Default to 'Guest' if not set
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <div class="dashboard">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>

        <?php if ($user_role == 'Admin') : ?>
            <div class="admin-links">
                <h3>Admin Dashboard</h3>
                <a href="manage_books.php">Manage Books</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="reports.php">View Reports</a>
            </div>
        <?php else : ?>
            <div class="user-links">
                <h3>User Dashboard</h3>
                <a href="search_books.php">Search Books</a>
                <a href="borrow_books.php">Borrow Books</a>
                <a href="view_history.php">View Borrowing History</a>
                <a href="return_books.php">Return Borrowed Books</a> <!-- Link to return books -->
            </div>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
    </div>
</div>

<footer>
    <p>&copy; 2024 Library Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
