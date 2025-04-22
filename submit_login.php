<?php
session_start(); // Start the session

// Establishing a database connection (Replace placeholders with actual values)
$servername = "localhost";
$username = "root";
$password = "Frankfurt018";
$database = "csresearch";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving user inputs
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check if the entered credentials exist in the database
    $sql_check_credentials = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql_check_credentials);

    if ($result->num_rows > 0) {
        // Valid credentials, set session variable and redirect to dashboard
        $_SESSION['loggedin'] = true;
        header("Location: options.php");
        exit; // Terminate script after redirect
    } else {
        // Invalid credentials, display an error message and redirect to admin.php
        echo "<script>alert('Incorrect login credentials!');</script>";
        echo "<script>window.location.href = 'admin.php';</script>";
        exit; // Terminate script after redirect
    }
}

// Close the database connection
$conn->close();
?>

