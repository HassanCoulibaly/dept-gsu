<?php
session_start(); // Start the session for managing login status and error messages

// Establishing a database connection
$servername = "127.0.0.1:3390";
$username = "root";
$password = "";
$database = "cs_research_new"; // Your correctly updated database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Log the error for debugging, but don't expose sensitive details to the user
    error_log("Database Connection failed: " . $conn->connect_error);
    $_SESSION['login_error'] = "A server error occurred. Please try again later.";
    header("Location: admin.php");
    exit;
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving user inputs and sanitizing them
    $email = $conn->real_escape_string($_POST['email']);
    $password_input = $_POST['password']; // Get the plain text password from the form

    // --- TEMPORARY: NO HASHING, DIRECT COMPARISON (INSECURE) ---
    // Prepare the SQL statement to prevent SQL Injection
    // We select the plain text password from the database based on the email.
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $plain_text_password_from_db = $user['password']; // Get the plain text password from DB

        // --- TEMPORARY: DIRECT PLAIN TEXT PASSWORD COMPARISON ---
        if ($password_input === $plain_text_password_from_db) {
            // Valid credentials, set session variable and redirect to dashboard
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_id'] = $user['id'];

            header("Location: dashboard.php");
            exit;
        } else {
            // Invalid password
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: admin.php");
            exit;
        }
    } else {
        // No user found with that email
        $_SESSION['login_error'] = "Invalid email or password.";
        header("Location: admin.php");
        exit;
    }

    $stmt->close(); // Close the prepared statement
}

// Close the database connection
$conn->close();
?>