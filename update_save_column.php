<?php
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

// Retrieve data from AJAX request
$data = json_decode(file_get_contents("php://input"), true);

// Update save column in the database
foreach ($data as $row) {
    $id = $conn->real_escape_string($row['id']); // Sanitize input to prevent SQL injection
    $save = $conn->real_escape_string($row['save']);
    $sql = "UPDATE application SET save='$save' WHERE id='$id'";
    $result = $conn->query($sql);
}

$conn->close();

// Send response
echo "Success";
?>
