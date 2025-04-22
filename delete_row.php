<?php
// Check if rowId is set and not empty
if(isset($_POST['rowId']) && !empty($_POST['rowId'])) {
    // Sanitize input to prevent SQL injection
    $rowId = $_POST['rowId'];

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

    // Prepare SQL statement to delete the row
    $sql = "DELETE FROM application WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameters and execute the statement
    $stmt->bind_param("i", $rowId); // Assuming 'id' is an integer, adjust the datatype ('i') if necessary
    $stmt->execute();

    // Check if the deletion was successful
    if($stmt->affected_rows > 0) {
        echo "Row deleted successfully.";
    } else {
        echo "Error deleting row.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>


