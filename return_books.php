<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle book return
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_book_id'])) {
    $book_id = $_POST['return_book_id'];
    $return_date = date('Y-m-d'); // Current date

    // Update the transaction to mark the book as returned
    $sql_return = "UPDATE Transactions SET ReturnDate = ? WHERE UserID = ? AND ResourceID = ? AND ReturnDate IS NULL";
    $stmt_return = $conn->prepare($sql_return);
    $stmt_return->bind_param("sii", $return_date, $user_id, $book_id);

    if ($stmt_return->execute()) {
        // Update the book's availability status to 'available'
        $sql_update_book = "UPDATE Books SET Status = 'available' WHERE ResourceID = ?";
        $stmt_update_book = $conn->prepare($sql_update_book);
        $stmt_update_book->bind_param("i", $book_id);
        $stmt_update_book->execute();

        $_SESSION['message'] = "Book returned successfully!";
    } else {
        $_SESSION['message'] = "Error returning the book.";
    }
}

// Fetch borrowed books that have not been returned
$sql = "SELECT b.ResourceID, b.Author, b.ISBN, b.Publisher, b.Edition, b.PublicationDate, t.BorrowDate, t.DueDate
        FROM Books b
        JOIN Transactions t ON b.ResourceID = t.ResourceID
        WHERE t.UserID = ? AND t.ReturnDate IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Borrowed Books</title>
    <style>
        /* General Page Layout */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            padding-bottom: 20px;
        }

        header h1 {
            font-size: 2.5em;
            color: #333;
            margin: 0;
        }

        h2 {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }

        table td {
            background-color: #fafafa;
            color: #333;
        }

        table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        table td a {
            color: #007BFF;
            text-decoration: none;
        }

        table td a:hover {
            text-decoration: underline;
        }

        /* Button Styling */
        .back-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 1.2em;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #45a049;
        }

        /* Success Message */
        .alert.success {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table th, table td {
                padding: 8px;
                font-size: 0.9em;
            }

            .back-button {
                width: 100%;
                font-size: 1.5em;
                padding: 12px 0;
            }
        }

    </style>
</head>
<body>

<!-- Back to Dashboard Button -->
<a href="dashboard.php" class="back-button">Back to Dashboard</a>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <h2>Return Borrowed Books</h2>

    <!-- Success Message -->
    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <!-- Borrowing History Table -->
    <table>
        <thead>
            <tr>
                <th>Author</th>
                <th>ISBN</th>
                <th>Publisher</th>
                <th>Edition</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Author']); ?></td>
                    <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
                    <td><?php echo htmlspecialchars($row['Publisher']); ?></td>
                    <td><?php echo htmlspecialchars($row['Edition']); ?></td>
                    <td><?php echo htmlspecialchars($row['DueDate']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="return_book_id" value="<?php echo $row['ResourceID']; ?>">
                            <button type="submit">Return</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>
