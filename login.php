<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $membership_id = $_POST['membership_id'];
    $password = $_POST['password'];

    // Query to check if the user exists
    $sql = "SELECT * FROM Users WHERE MembershipID = '$membership_id' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the hashed password with the entered password
        if (password_verify($password, $user['Password'])) {
            // Store user details in session
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['username'] = $user['UserType']; // Store UserType as the username
            
            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid credentials!";
        }
    } else {
        $_SESSION['message'] = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <header>
        <h1>Library Management System</h1>
    </header>

    <h2>Login</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert error'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <form method="POST">
        <input type="text" name="membership_id" placeholder="Membership ID" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

</body>
</html>
