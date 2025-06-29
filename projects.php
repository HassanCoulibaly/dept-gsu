<?php
session_start();
include 'config.php';

// Fetch all research projects
$query = "SELECT * FROM research ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Projects</title>
    <style>
        .project-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>All Projects</h2>

<?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <div class="project-card">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><strong>Short Description:</strong> <?= htmlspecialchars($row['short_description']) ?></p>
        <p><strong>Long Description:</strong> <?= htmlspecialchars($row['long_description']) ?></p>
        <p><strong>Requirement:</strong> <?= htmlspecialchars($row['requirement']) ?></p>
        <p><strong>Keyword:</strong> <?= htmlspecialchars($row['keyword']) ?></p>
        <p><strong>Paid:</strong> <?= htmlspecialchars($row['paid']) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($row['category']) ?></p>
        <?php if (!empty($row['image'])): ?>
            <p><img src="success_image/<?= htmlspecialchars($row['image']) ?>" width="200"></p>
        <?php endif; ?>

        <?php if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true): ?>
            <p>
                <a href="edit_project.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="delete_project.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
            </p>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

</body>
</html>
