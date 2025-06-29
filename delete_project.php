<?php
session_start();
include 'config.php';

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin.php");
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Optionally, fetch and delete the image from disk (optional)
    $stmt = mysqli_prepare($conn, "SELECT image FROM research WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $project = mysqli_fetch_assoc($result);
    if ($project && !empty($project['image']) && file_exists('./success_image/' . $project['image'])) {
        unlink('./success_image/' . $project['image']); // delete file
    }

    // Delete project
    $stmt = mysqli_prepare($conn, "DELETE FROM research WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header("Location: projects.php");
exit();
