<?php
session_start();
include('db.php');

// Check if the user is logged in and has Admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Example: Fetching book data for the report (adjust to your needs)
$stmt = $conn->prepare("SELECT * FROM Books ORDER BY PublicationDate DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports - Library Management System</title>
    <style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        color: #333;
    }

    header {
        background-color: #333;
        color: #fff;
        padding: 20px;
        text-align: center;
    }

    h1 {
        margin: 0;
        font-size: 28px;
    }

    nav a {
        color: #fff;
        text-decoration: none;
        margin: 0 15px;
        font-size: 18px;
    }

    nav a:hover {
        text-decoration: underline;
    }

    .report-container {
        max-width: 1000px;
        margin: 30px auto;
        background-color: #fff;
        padding: 30px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    h2 {
        font-size: 24px;
        color: #333;
        text-align: center;
        margin-bottom: 20px;
    }

    /* Table Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #333;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    /* Button Styles */
    .btn {
        padding: 12px 20px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 16px;
        display: inline-block;
        margin-top: 20px;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #45a049;
    }

    .btn-logout {
        background-color: #f44336;
    }

    .btn-logout:hover {
        background-color: #e53935;
    }

    /* Footer Styles */
    footer {
        background-color: #333;
        color: white;
        text-align: center;
        padding: 15px;
        position: fixed;
        width: 100%;
        bottom: 0;
    }

</style>
</head>
<body>

    <!-- Back to Dashboard Button -->
    <a href="dashboard.php" class="back-button">Back to Dashboard</a>

<header>
    <h1>Library Management System - Reports</h1>
    <nav>
        <a href="manage_books.php">Manage Books</a> 
    </nav>
</header>

<div class="report-container">
    <h2>Book Reports</h2>

    <!-- Displaying fetched book data -->
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Publisher</th>
            <th>Edition</th>
            <th>Publication Date</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Title']); ?></td>
            <td><?php echo htmlspecialchars($row['Author']); ?></td>
            <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
            <td><?php echo htmlspecialchars($row['Publisher']); ?></td>
            <td><?php echo htmlspecialchars($row['Edition']); ?></td>
            <td><?php echo htmlspecialchars($row['PublicationDate']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
