<?php
session_start();
include('db.php');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch the user to edit based on user ID
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM Users WHERE UserID = '$user_id' LIMIT 1";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();

    if (!$user) {
        die("User not found.");
    }

    // Handling the form submission to update user info
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $user_type = $_POST['user_type'];
        $role = $_POST['role'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];

        // Update the user in the database
        $update_sql = "UPDATE Users SET FirstName = '$first_name', LastName = '$last_name', UserType = '$user_type', Role = '$role', Email = '$email', PhoneNumber = '$phone_number' WHERE UserID = '$user_id'";
        if ($conn->query($update_sql) === TRUE) {
            $_SESSION['message'] = "User updated successfully!";
            header("Location: manage_users.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <h2>Edit User</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <!-- Edit User Form -->
    <form method="POST">
        <input type="text" name="first_name" value="<?php echo $user['FirstName']; ?>" required><br>
        <input type="text" name="last_name" value="<?php echo $user['LastName']; ?>" required><br>
        <select name="user_type" required>
            <option value="Student" <?php echo ($user['UserType'] == 'Student') ? 'selected' : ''; ?>>Student</option>
            <option value="Faculty" <?php echo ($user['UserType'] == 'Faculty') ? 'selected' : ''; ?>>Faculty</option>
            <option value="Staff" <?php echo ($user['UserType'] == 'Staff') ? 'selected' : ''; ?>>Staff</option>
        </select><br>
        <select name="role" required>
            <option value="User" <?php echo ($user['Role'] == 'User') ? 'selected' : ''; ?>>User</option>
            <option value="Admin" <?php echo ($user['Role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
        </select><br>
        <input type="email" name="email" value="<?php echo $user['Email']; ?>" required><br>
        <input type="text" name="phone_number" value="<?php echo $user['PhoneNumber']; ?>" required><br>
        <input type="submit" value="Update User">
    </form>

</div>

</body>
</html>
