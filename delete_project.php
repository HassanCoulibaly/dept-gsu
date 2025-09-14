<?php
// delete_project.php

session_start(); // Start the session to access login status

// Include database configuration
// Ensure config.php establishes a $conn variable for the database connection
include 'config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, send an error response and exit
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the project ID from the POST data
    // Use filter_input for safer retrieval
    $project_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    // Validate the project ID
    if ($project_id === false || $project_id === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid project ID provided.']);
        exit();
    }

    // Start a transaction for atomicity (optional but good practice for related operations)
    mysqli_begin_transaction($conn);

    try {
        // --- Optional: Fetch image URL before deleting the project row ---
        // This allows you to delete the associated image file from the server.
        $image_url_query = "SELECT image_url FROM projects WHERE id = ?";
        $stmt_fetch_image = mysqli_prepare($conn, $image_url_query);
        if ($stmt_fetch_image) {
            mysqli_stmt_bind_param($stmt_fetch_image, "i", $project_id);
            mysqli_stmt_execute($stmt_fetch_image);
            $result_image = mysqli_stmt_get_result($stmt_fetch_image);
            $project_data = mysqli_fetch_assoc($result_image);
            mysqli_stmt_close($stmt_fetch_image);

            $image_file_to_delete = null;
            if ($project_data && !empty($project_data['image_url'])) {
                $image_file_to_delete = './success_image/' . $project_data['image_url'];
            }
        } else {
            // Log this error, but don't stop deletion if image URL fetch fails
            error_log("Failed to prepare statement for fetching image_url: " . mysqli_error($conn));
        }

        // Prepare the DELETE statement
        $delete_query = "DELETE FROM projects WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);

        if ($stmt) {
            // Bind the project ID parameter
            mysqli_stmt_bind_param($stmt, "i", $project_id); // "i" for integer

            // Execute the statement
            $delete_success = mysqli_stmt_execute($stmt);

            if ($delete_success) {
                // Check if any rows were affected
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    // If project deleted from DB, attempt to delete the image file
                    if ($image_file_to_delete && file_exists($image_file_to_delete)) {
                        if (!unlink($image_file_to_delete)) {
                            // Log if image deletion fails, but don't roll back DB transaction
                            error_log("Failed to delete image file: " . $image_file_to_delete);
                        }
                    }
                    mysqli_commit($conn); // Commit the transaction
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Project deleted successfully.']);
                } else {
                    // No rows affected means project ID not found
                    mysqli_rollback($conn); // Rollback if nothing was deleted
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Project not found or already deleted.']);
                }
            } else {
                // Error during execution
                mysqli_rollback($conn); // Rollback the transaction on error
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error deleting project: ' . mysqli_error($conn)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Error preparing statement
            mysqli_rollback($conn); // Rollback the transaction on error
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement.']);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn); // Ensure rollback on any exception
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
    }

} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Close the database connection
mysqli_close($conn);
?>