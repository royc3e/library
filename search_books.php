<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_term = $_POST['search_term'];
    
    // Query to search books by title, author, or category
    $sql = "SELECT * FROM Books WHERE Title LIKE ? OR Author LIKE ? OR Category LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term_wildcard = "%" . $search_term . "%";
    $stmt->bind_param("sss", $search_term_wildcard, $search_term_wildcard, $search_term_wildcard);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    } else {
        $_SESSION['message'] = "No books found matching your search!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <h2>Search Books</h2>

    <form method="POST">
        <input type="text" name="search_term" placeholder="Search by title, author, or category" required>
        <input type="submit" value="Search">
    </form>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert error'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <?php if (!empty($search_results)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($search_results as $book) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['Title']); ?></td>
                        <td><?php echo htmlspecialchars($book['Author']); ?></td>
                        <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                        <td><?php echo htmlspecialchars($book['Category']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

</body>
</html>
