<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Clear History
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_history'])) {
    // SQL query to delete all transactions with a non-null ReturnDate
    $sql_clear_history = "DELETE FROM Transactions WHERE UserID = ? AND ReturnDate IS NOT NULL";
    $stmt_clear_history = $conn->prepare($sql_clear_history);
    $stmt_clear_history->bind_param("i", $user_id);

    // Execute the query
    if ($stmt_clear_history->execute()) {
        $_SESSION['message'] = "Returned books history cleared successfully!";
    } else {
        $_SESSION['message'] = "Error clearing the returned books history.";
    }
}

// Fetch borrowing history
$sql = "SELECT b.Author, b.ISBN, b.Publisher, b.Edition, t.BorrowDate, t.DueDate, t.ReturnDate 
        FROM Books b
        JOIN Transactions t ON b.ResourceID = t.ResourceID
        WHERE t.UserID = ?";
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
    <title>View Borrowing History</title>
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

        /* Highlight borrowed books */
        table tr.borrowed {
            background-color: #fffae6; /* Light yellow to highlight borrowed books */
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

        /* Clear History Button Styling */
        .clear-button {
            background-color: #f44336; /* Red color */
            color: white;
            padding: 10px 20px;
            font-size: 1.2em;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            cursor: pointer;
            margin-top: 20px;
        }

        .clear-button:hover {
            background-color: #e53935; /* Darker red */
        }

        /* Footer Styling */
        footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        footer p {
            margin: 0;
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

    <h2>Your Borrowing History</h2>

    <!-- Success Message -->
    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <!-- Clear History Button -->
    <form method="POST" style="text-align: left; margin-top: 20px;">
        <button type="submit" name="clear_history" class="clear-button">Clear Returned Books</button>
    </form>

    <!-- Borrowing History Table -->
    <table>
        <thead>
            <tr>
                <th>Author</th>
                <th>ISBN</th>
                <th>Publisher</th>
                <th>Edition</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr <?php echo ($row['ReturnDate'] == NULL) ? 'class="borrowed"' : ''; ?>>
                    <td><?php echo htmlspecialchars($row['Author']); ?></td>
                    <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
                    <td><?php echo htmlspecialchars($row['Publisher']); ?></td>
                    <td><?php echo htmlspecialchars($row['Edition']); ?></td>
                    <td>
                        <?php
                        // Convert BorrowDate to a more readable format
                        $borrow_date = strtotime($row['BorrowDate']);
                        echo date("l, F j, Y", $borrow_date);
                        ?>
                    </td>
                    <td>
                        <?php
                        // Convert DueDate to a more readable format
                        $due_date = strtotime($row['DueDate']);
                        echo date("l, F j, Y", $due_date);
                        ?>
                    </td>
                    <td>
                        <?php 
                        if ($row['ReturnDate'] == NULL) {
                            echo "Not Returned";
                        } else {
                            // Convert ReturnDate to a more readable format
                            $return_date = strtotime($row['ReturnDate']);
                            echo date("l, F j, Y", $return_date);
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<footer>
    <p>&copy; 2024 Library Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
