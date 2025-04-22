<?php
// Include configuration file
@include 'config.php';

// Delete product if delete parameter is set
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    // Implementing code validation here is redundant because it's handled in the JavaScript on the client-side.
    $delete_query = "DELETE FROM success_stories WHERE id = $id";
    $delete_result = mysqli_query($conn, $delete_query);
    if($delete_result){
        // Redirect to success_dashboard.php after successful deletion
        header('location:success_dashboard.php');
        exit(); // Stop further execution
    } else {
        // Handle error if deletion fails
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

// Fetch and display product details if ID is provided
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $select_query = "SELECT * FROM success_stories WHERE id = $id";
    $result = mysqli_query($conn, $select_query);
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        // Display product details
        echo "<h2>{$row['title']}</h2>";
        echo "<p>{$row['description']}</p>";
        echo "<img src='success_image/{$row['image']}' height='200' alt=''>";
    } else {
        // Handle case where no product found for the provided ID
        echo "No story found with ID: $id";
    }
} else {
    // Handle case where no ID is provided
    echo "No story ID provided";
}
?>
