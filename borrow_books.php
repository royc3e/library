<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if a book is being borrowed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_book_id'])) {
    $book_id = $_POST['borrow_book_id'];
    $borrow_date = date('Y-m-d'); // Current date
    $due_date = date('Y-m-d', strtotime('+7 days')); // 7 days from today as due date

    // Check if the user has already borrowed this book
    $sql_check = "SELECT * FROM Transactions WHERE UserID = ? AND ResourceID = ? AND Status = 'borrowed' AND ReturnDate IS NULL";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $book_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['message'] = "You have already borrowed this book!";
    } else {
        // Insert a new borrowing transaction into the Transactions table
        $sql_borrow = "INSERT INTO Transactions (UserID, ResourceID, BorrowDate, DueDate, Status) VALUES (?, ?, ?, ?, 'borrowed')";
        $stmt_borrow = $conn->prepare($sql_borrow);
        $stmt_borrow->bind_param("iiss", $user_id, $book_id, $borrow_date, $due_date);

        if ($stmt_borrow->execute()) {
            $_SESSION['message'] = "Book borrowed successfully! Please return it by the due date.";
        } else {
            $_SESSION['message'] = "Error borrowing the book!";
        }
    }
}

// Fetch all available books that the user can borrow, including the title
$sql = "SELECT ResourceID, Title, Author, ISBN, Publisher, Edition, PublicationDate FROM Books WHERE ResourceID NOT IN (SELECT ResourceID FROM Transactions WHERE UserID = ? AND Status = 'borrowed' AND ReturnDate IS NULL)";
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
    <title>Borrow Books</title>
    <style>
        /* Basic styling for the page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 36px;
            color: #333;
        }

        h2 {
            color: #4CAF50;
        }

        /* Message styles */
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success {
            background-color: #4CAF50;
            color: white;
        }

        .error {
            background-color: #f44336;
            color: white;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Button styling */
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        form {
            display: inline;
        }

        /* Responsive design for mobile */
        @media screen and (max-width: 768px) {
            .container {
                width: 90%;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }

            button {
                padding: 8px 16px;
                font-size: 12px;
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

    <h2>Borrow Books</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <h3>Available Books</h3>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Publisher</th>
                <th>Edition</th>
                <th>Publication Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($book = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['Title']); ?></td>
                    <td><?php echo htmlspecialchars($book['Author']); ?></td>
                    <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                    <td><?php echo htmlspecialchars($book['Publisher']); ?></td>
                    <td><?php echo htmlspecialchars($book['Edition']); ?></td>
                    <td> <?php
                        // Convert PublicationDate to a more readable format
                        $publication_date = strtotime($book['PublicationDate']);
                        echo date("l, F j, Y", $publication_date); // e.g., "Monday, January 1, 2024"
                        ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="borrow_book_id" value="<?php echo $book['ResourceID']; ?>">
                            <button type="submit">Borrow</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
