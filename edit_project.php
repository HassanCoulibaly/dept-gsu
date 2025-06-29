<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Ensure admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin.php");
    exit();
}

// Get the ID of the project to be updated
$id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
if (!$id) {
    die("Invalid project ID.");
}

// Message holder
$message = [];

// Hardcoded categories (you can also fetch from DB)
$categories = ['AI', 'Web', 'Data Science', 'Security', 'Mobile'];

// Handle form submission
if (isset($_POST['update_product'])) {
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $requirement = $_POST['requirement'];
    $keyword = $_POST['keyword'];
    $paid = $_POST['paid'];
    $category = $_POST['category'];
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    if (empty($title) || empty($short_description) || empty($long_description)) {
        $message[] = 'Please fill out all required fields.';
    } else {
        // Get current image if new image not uploaded
        if (empty($image)) {
            $stmt = mysqli_prepare($conn, "SELECT image FROM research WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $image = $row['image'];
            mysqli_stmt_close($stmt);
        } else {
            // Save new image
            $target_path = './success_image/' . basename($image);
            move_uploaded_file($image_tmp_name, $target_path);
        }

        // Prepare and execute update
        $query = "UPDATE research SET title=?, short_description=?, long_description=?, requirement=?, keyword=?, paid=?, category=?, image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssssi", $title, $short_description, $long_description, $requirement, $keyword, $paid, $category, $image, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: project_dashboard.php");
                exit();
            } else {
                $message[] = "Could not update the project.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message[] = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Project</title>
    <link rel="stylesheet" href="edit_project.css">
    <style>
        #short_description, #long_description {
            width: 500px;
            resize: vertical;
        }
    </style>
    <script>
        function countCharacters(id, max) {
            let el = document.getElementById(id);
            let count = el.value.length;
            if (count > max) {
                el.value = el.value.slice(0, max);
                alert("Maximum character limit reached");
            }
            document.getElementById(id + "_char_count").innerText = (max - count) + " characters remaining";
        }

        function countWords(id, max) {
            let el = document.getElementById(id);
            let words = el.value.trim().split(/\s+/);
            if (words.length > max) {
                el.value = words.slice(0, max).join(" ");
                alert("Maximum word limit reached");
            }
            document.getElementById(id + "_word_count").innerText = (max - words.length) + " words remaining";
        }
    </script>
</head>
<body>

<?php foreach ($message as $msg): ?>
    <p style="color: red; padding-left: 10px;\"><?= htmlspecialchars($msg) ?></p>
<?php endforeach; ?>

<div class="container">
    <div class="admin-project-form-container centered">

        <?php
        $select = mysqli_query($conn, "SELECT * FROM research WHERE id = $id");
        if ($row = mysqli_fetch_assoc($select)):
        ?>

        <form method="post" enctype="multipart/form-data">
            <h3 class="title">Update the Project</h3>

            <label>Title</label>
            <input type="text" class="box" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>

            <label>Short Description</label>
            <span id="short_description_char_count">500 characters remaining</span>
            <textarea id="short_description" name="short_description" class="box" oninput="countCharacters('short_description', 500)"><?= htmlspecialchars($row['short_description']) ?></textarea>

            <label>Long Description</label>
            <span id="long_description_word_count">1000 words remaining</span>
            <textarea id="long_description" name="long_description" class="box" oninput="countWords('long_description', 1000)"><?= htmlspecialchars($row['long_description']) ?></textarea>

            <label>Requirements</label>
            <input type="text" class="box" name="requirement" value="<?= htmlspecialchars($row['requirement']) ?>">

            <label>Keywords</label>
            <input type="text" class="box" name="keyword" value="<?= htmlspecialchars($row['keyword']) ?>">

            <label>Pay</label>
            <select name="paid" class="box">
                <option value="yes" <?= $row['paid'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                <option value="no" <?= $row['paid'] === 'no' ? 'selected' : '' ?>>No</option>
            </select>

            <label>Category</label>
            <select name="category" class="box">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $row['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>

            <label>Update Image</label>
            <?php if (!empty($row['image'])): ?>
                <br><img src="success_image/<?= htmlspecialchars($row['image']) ?>" width="150"><br>
            <?php endif; ?>
            <input type="file" name="image" accept="image/png, image/jpeg, image/jpg" class="box">

            <input type="submit" name="update_product" value="Update Project" class="btn">
        </form>

        <?php else: ?>
            <p>Project not found.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
