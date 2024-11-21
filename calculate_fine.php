<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $resource_id = $_POST['resource_id'];
    $borrow_date = date("Y-m-d");
    $due_date = date('Y-m-d', strtotime('+14 days')); // Due date is 14 days from borrow date
    
    // Insert into BorrowingTransactions table
    $sql = "INSERT INTO BorrowingTransactions (UserID, ResourceID, BorrowDate, DueDate)
            VALUES ('$user_id', '$resource_id', '$borrow_date', '$due_date')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Book borrowed successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<form method="POST">
    User ID: <input type="text" name="user_id" required><br>
    Resource ID: <input type="text" name="resource_id" required><br>
    <input type="submit" value="Borrow Book">
</form>
