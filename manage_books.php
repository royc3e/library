<?php
session_start();
include('db.php');

// Check if the user is logged in and is an Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    // Get form data
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $publisher = $_POST['publisher'];
    $edition = $_POST['edition'];
    $publicationDate = $_POST['publicationDate'];

    // Step 1: Check if ISBN already exists
    $isbn_check_sql = "SELECT * FROM Books WHERE ISBN = ?";
    $stmt = $conn->prepare($isbn_check_sql);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Error: ISBN already exists!";
        $_SESSION['message_type'] = "error";
    } else {
        // Step 2: Insert into the libraryresources table
        $stmt = $conn->prepare("INSERT INTO libraryresources (Publisher, Edition) VALUES (?, ?)");
        $stmt->bind_param("ss", $publisher, $edition);
        if ($stmt->execute()) {
            $resourceID = $stmt->insert_id; // Get the ResourceID
            $stmt->close();

            // Step 3: Insert into Books table using the ResourceID
            $stmt = $conn->prepare("INSERT INTO Books (ResourceID, Title, Author, ISBN, Publisher, Edition, PublicationDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $resourceID, $title, $author, $isbn, $publisher, $edition, $publicationDate);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Book added successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error: " . $conn->error;
                $_SESSION['message_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
    }

    // Redirect to refresh and show the message
    header("Location: manage_books.php");
    exit();
}

// Fetch existing books
$sql = "SELECT * FROM Books";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            padding-bottom: 20px;
        }

        header h1 {
            font-size: 2.5em;
            color: #333;
        }

        h2 {
            font-size: 2em;
            color: #333;
            margin-bottom: 20px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .alert.success {
            background-color: #4CAF50;
            color: white;
        }
        .alert.error {
            background-color: #f44336;
            color: white;
        }

        form input[type="text"], form input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1.2em;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        form button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
        }

        table td {
            background-color: #fafafa;
        }

        table a {
            text-decoration: none;
            color: #007BFF;
        }

        table a:hover {
            text-decoration: underline;
        }

        table td a {
            margin-right: 10px;
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

        <h2>Manage Books</h2>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <!-- Add New Book -->
        <form method="POST">
            <input type="text" name="title" placeholder="Title" required><br>
            <input type="text" name="author" placeholder="Author" required><br>
            <input type="text" name="isbn" placeholder="ISBN" required><br>
            <input type="text" name="publisher" placeholder="Publisher" required><br>
            <input type="text" name="edition" placeholder="Edition" required><br>
            <input type="date" name="publicationDate" placeholder="Publication Date" required><br>
            <button type="submit" name="add_book">Add Book</button>
        </form>

        <h3>Existing Books</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Publisher</th>
                    <th>Edition</th>
                    <th>Publication Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['Title']) . "</td>
                                <td>" . htmlspecialchars($row['Author']) . "</td>
                                <td>" . htmlspecialchars($row['ISBN']) . "</td>
                                <td>" . htmlspecialchars($row['Publisher']) . "</td>
                                <td>" . htmlspecialchars($row['Edition']) . "</td>
                                <td>" . htmlspecialchars($row['PublicationDate']) . "</td>
                                <td>
                                    <a href='edit_book.php?id=" . $row['ResourceID'] . "'>Edit</a> | 
                                    <a href='delete_book.php?id=" . $row['ResourceID'] . "'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No books available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
