<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $user_type = $_POST['user_type'];
    $role = $_POST['role']; // 'User' or 'Admin'
    $membership_id = $_POST['membership_id'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password']; // Get the password from the form
    $registration_date = date("Y-m-d");
    $max_books_allowed = ($user_type == 'Student') ? 3 : 5;

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL query
    $sql = "INSERT INTO Users (FirstName, LastName, UserType, Role, MembershipID, Email, PhoneNumber, Password, RegistrationDate, MaxBooksAllowed)
            VALUES ('$first_name', '$last_name', '$user_type', '$role', '$membership_id', '$email', '$phone_number', '$hashed_password', '$registration_date', '$max_books_allowed')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "User registered successfully!";
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <h2>Register</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br>
        <select name="user_type" required>
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Staff">Staff</option>
        </select><br>
        <select name="role" required>
            <option value="User">User</option>
            <option value="Admin">Admin</option>
        </select><br>
        <input type="text" name="membership_id" placeholder="Membership ID" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="phone_number" placeholder="Phone Number" required><br>
        <input type="password" name="password" placeholder="Password" required><br> <!-- New field for password -->
        <input type="submit" value="Register">
    </form>


    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
