<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "Frankfurt018";
$dbname = "csresearch";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind SQL statement
$stmt = $conn->prepare("INSERT INTO application (first_name, last_name, pantherid, upload_pdf, checkbox_options, why_hire_text, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssissss", $first_name, $last_name, $pantherid, $upload_pdf, $checkbox_options, $why_hire_text, $email);

// Set parameters and execute
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$upload_pdf = file_get_contents($_FILES['upload_pdf']['tmp_name']);

// Validate PantherID
$pantherid = $_POST['pantherid'];
if (!preg_match('/^\d{9}$/', $pantherid)) {
    // If PantherID is not valid, display an alert and redirect back to the form
    echo '<script>alert("Panther ID is not valid.");';
    echo 'window.location.href = "application.php";</script>';
    exit; // Stop further execution
}

// Handle checkbox options
if (isset($_POST['checkbox_options'])) {
    if (is_array($_POST['checkbox_options'])) {
        $checkbox_options = implode(", ", $_POST['checkbox_options']);
    } else {
        $checkbox_options = $_POST['checkbox_options'];
    }
} else {
    $checkbox_options = '';
}

$why_hire_text = $_POST['why_hire_text'];

$stmt->execute();

$stmt->close();
$conn->close();

// JavaScript redirection and popup
echo '<script>';
echo 'alert("Congratulations, sent successfully.");';
echo 'window.location.href = "projects.php";';
echo '</script>';
?>




