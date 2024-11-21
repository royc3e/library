<?php
session_start();
include('db.php');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Get the book ID from the URL
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Fetch the book details from the database
    $stmt = $conn->prepare("SELECT * FROM Books WHERE ResourceID = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    // If no book found, redirect
    if (!$book) {
        $_SESSION['message'] = "Book not found.";
        $_SESSION['message_type'] = "error";
        header("Location: manage_books.php");
        exit();
    }
}

// Handle the delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Delete associated transactions first
    $stmt_delete_transactions = $conn->prepare("DELETE FROM Transactions WHERE ResourceID = ?");
    $stmt_delete_transactions->bind_param("i", $book_id);
    $stmt_delete_transactions->execute();
    $stmt_delete_transactions->close();

    // Now, delete the book
    $stmt = $conn->prepare("DELETE FROM Books WHERE ResourceID = ?");
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Book deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting the book.";
        $_SESSION['message_type'] = "error";
    }

    // Redirect to manage_books.php after the delete operation
    header("Location: manage_books.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book - Library Management System</title>
    <style>
        /* Basic styling for the delete book confirmation */
        .confirmation-container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .confirmation-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .book-details {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: left;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .book-details p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }

        .book-details p strong {
            color: #333;
        }

        button, a {
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 16px;
        }

        .btn-confirm {
            background-color: #4CAF50;
            border: none;
            cursor: pointer;
        }

        .btn-confirm:hover {
            background-color: #45a049;
        }

        .btn-cancel {
            background-color: #f44336;
            border: none;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #e53935;
        }

        form {
            display: inline-block;
        }

        a.btn-cancel {
            display: inline-block;
            text-align: center;
        }

        /* Basic styling for alert messages */
        .alert {
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        /* Success message */
        .alert.success {
            background-color: #4CAF50;
            color: white;
        }

        /* Error message */
        .alert.error {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <div class="confirmation-container">
        <h2>Are you sure you want to delete this book?</h2>
        
        <!-- Display book details -->
        <div class="book-details">
            <p><strong>Title:</strong> <?php echo htmlspecialchars($book['Title']); ?></p>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['Author']); ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['ISBN']); ?></p>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($book['Publisher']); ?></p>
            <p><strong>Edition:</strong> <?php echo htmlspecialchars($book['Edition']); ?></p>
            <p><strong>Publication Date:</strong> <?php echo htmlspecialchars($book['PublicationDate']); ?></p>
        </div>

        <!-- Success/Error Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>

        <!-- Delete Confirmation Form -->
        <form method="POST">
            <button type="submit" name="confirm_delete" class="btn-confirm">Confirm Delete</button>
        </form>
        <a href="manage_books.php" class="btn-cancel">Cancel</a>
    </div>
</div>

<footer>
    <p>&copy; 2024 Library Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
