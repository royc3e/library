<?php
session_start();
include('db.php');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Check if book ID is provided
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Fetch the book details to edit
    $stmt = $conn->prepare("SELECT * FROM Books WHERE ID = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();

    if (!$book) {
        echo "Book not found!";
        exit();
    }

    // Handle form submission to update book
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_book'])) {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];
        $category = $_POST['category'];

        // Prepare and bind
        $stmt = $conn->prepare("UPDATE Books SET Title = ?, Author = ?, ISBN = ?, Category = ? WHERE ID = ?");
        $stmt->bind_param("ssssi", $title, $author, $isbn, $category, $book_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book updated successfully!";
            header("Location: manage_books.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }

        $stmt->close();
    }
} else {
    echo "Invalid book ID!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Edit Book</h1>
    </header>

    <form method="POST">
        <input type="text" name="title" placeholder="Book Title" value="<?= htmlspecialchars($book['Title']) ?>" required><br>
        <input type="text" name="author" placeholder="Author" value="<?= htmlspecialchars($book['Author']) ?>" required><br>
        <input type="text" name="isbn" placeholder="ISBN" value="<?= htmlspecialchars($book['ISBN']) ?>" required><br>
        <input type="text" name="category" placeholder="Category" value="<?= htmlspecialchars($book['Category']) ?>" required><br>
        <button type="submit" name="update_book">Update Book</button>
    </form>
</div>

</body>
</html>
