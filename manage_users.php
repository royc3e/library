<?php
session_start();
include('db.php');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

// Handling user addition
if (isset($_POST['add_user'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $user_type = $_POST['user_type'];
    $role = $_POST['role'];
    $membership_id = $_POST['membership_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Insert new user into the database
    $insert_sql = "INSERT INTO Users (FirstName, LastName, UserType, Role, MembershipID, Email, PhoneNumber, Password) 
                   VALUES ('$first_name', '$last_name', '$user_type', '$role', '$membership_id', '$email', '$phone', '$password')";

    if ($conn->query($insert_sql) === TRUE) {
        $_SESSION['message'] = "User added successfully!";
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch all users from the database
$sql = "SELECT * FROM Users";
$result = $conn->query($sql);

// Handling user deletion
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];

    // Delete user from the database
    $delete_sql = "DELETE FROM Users WHERE UserID = '$user_id'";
    if ($conn->query($delete_sql) === TRUE) {
        $_SESSION['message'] = "User deleted successfully!";
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        header h1 {
            font-size: 2.5em;
            color: #333;
            text-align: center;
        }

        h2, h3 {
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .alert {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border-radius: 5px;
        }

        /* Button Style */
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            text-align: center;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #218838;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            padding-top: 60px;
            overflow-y: auto; 
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-content h2 {
            margin-top: 0;
            color: #333;
            text-align: center;
        }

        .modal label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 16px;
        }

        .modal input[type="text"], 
        .modal input[type="email"], 
        .modal input[type="password"],
        .modal select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .modal button[type="submit"] {
            width: 100%;
            background-color: #28a745;
            border: none;
            padding: 15px;
            color: white;
            font-size: 18px;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal button[type="submit"]:hover {
            background-color: #218838;
        }

        .modal button[type="button"] {
            width: 100%;
            background-color: #dc3545;
            border: none;
            padding: 15px;
            color: white;
            font-size: 18px;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal button[type="button"]:hover {
            background-color: #c82333;
        }

        /* Close Button Style */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 30px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
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

        <h2>Manage Users</h2>

        <!-- Add New User Form -->
        <button onclick="document.getElementById('addUserForm').style.display='block'">Add New User</button>

        <div id="addUserForm" class="modal">
            <form method="POST" class="modal-content">
                <span onclick="document.getElementById('addUserForm').style.display='none'" class="close">&times;</span>
                <h2>Add New User</h2>
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" required>

                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" required>

                <label for="user_type">User Type:</label>
                <input type="text" name="user_type" required>

                <label for="role">Role:</label>
                <select name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>

                <label for="membership_id">Membership ID:</label>
                <input type="text" name="membership_id" required>

                <label for="email">Email:</label>
                <input type="email" name="email" required>

                <label for="phone">Phone:</label>
                <input type="text" name="phone" required>

                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <button type="submit" name="add_user">Add User</button>
                <button type="button" onclick="document.getElementById('addUserForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- Display Success/Failure Message -->
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert success'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']);
        }
        ?>

        <h3>All Users</h3>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>User Type</th>
                    <th>Role</th>
                    <th>Membership ID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $user['FirstName']; ?></td>
                        <td><?php echo $user['LastName']; ?></td>
                        <td><?php echo $user['UserType']; ?></td>
                        <td><?php echo $user['Role']; ?></td>
                        <td><?php echo $user['MembershipID']; ?></td>
                        <td><?php echo $user['Email']; ?></td>
                        <td><?php echo $user['PhoneNumber']; ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['UserID']; ?>">Edit</a> | 
                            <a href="manage_users.php?delete_id=<?php echo $user['UserID']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <script>
        // Modal functionality
        window.onclick = function(event) {
            if (event.target == document.getElementById('addUserForm')) {
                document.getElementById('addUserForm').style.display = "none";
            }
        }
    </script>

</body>
</html>
