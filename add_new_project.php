<?php
session_start();
include 'config.php';

// Protect the form from unauthorized access
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin.php");
    exit();
}

// Categories (hardcoded or fetched from DB — your choice)
$categories = ['AI', 'Web', 'Data Science', 'Security', 'Mobile'];

// Handle form submit
if (isset($_POST['add_project'])) {
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $requirement = $_POST['requirement'];
    $keyword = $_POST['keyword'];
    $paid = $_POST['paid'];
    $category = $_POST['category'];
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    $image_folder = './success_image/' . $image;

    $insert_query = "INSERT INTO research (title, short_description, long_description, requirement, keyword, paid, category, image)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $title, $short_description, $long_description, $requirement, $keyword, $paid, $category, $image);
        $upload = mysqli_stmt_execute($stmt);
        if ($upload) {
            if (!empty($image)) {
                move_uploaded_file($image_tmp_name, $image_folder);
            }
            header('Location: project_dashboard.php');
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Add Project</title>
   <link rel="stylesheet" href="add_new_project.css">
   <style>
    #short_description, #long_description {
        width: 500px;
        resize: vertical;
    }
   </style>
   <script>
    function countWords(textareaId, maxWords) {
        var textarea = document.getElementById(textareaId);
        var words = textarea.value.trim().split(/\s+/).length;
        if (words > maxWords) {
            textarea.value = textarea.value.trim().split(/\s+/).slice(0, maxWords).join(" ");
            alert("Maximum word limit reached");
        }
        document.getElementById(textareaId + "_word_count").textContent = (maxWords - words) + " words remaining";
    }

    function countCharacters(textareaId, maxChars) {
        var textarea = document.getElementById(textareaId);
        var chars = textarea.value.length;
        if (chars > maxChars) {
            textarea.value = textarea.value.substring(0, maxChars);
            alert("Maximum character limit reached");
        }
        document.getElementById(textareaId + "_char_count").textContent = (maxChars - chars) + " characters remaining";
    }
   </script>
</head>
<body>
<div class="container">
   <div class="admin-product-form-container">
      <form action="" method="post" enctype="multipart/form-data">
         <h3>Add a New Project</h3>

         <label>Title</label>
         <input type="text" name="title" class="box" required>

         <label>Short Description</label>
         <span id="short_description_char_count">500 characters remaining</span>
         <textarea name="short_description" id="short_description" class="box" oninput="countCharacters('short_description', 500)" required></textarea>

         <label>Long Description</label>
         <span id="long_description_word_count">1000 words remaining</span>
         <textarea name="long_description" id="long_description" class="box" oninput="countWords('long_description', 1000)" required></textarea>

         <label>Requirements</label>
         <input type="text" name="requirement" class="box" required>

         <label>Keywords</label>
         <input type="text" name="keyword" class="box" required>

         <label>Paid</label>
         <select name="paid" class="box" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
         </select>

         <label>Category</label>
         <select name="category" class="box" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
         </select>

         <label>Upload Image</label>
         <input type="file" name="image" accept="image/*" class="box">

         <input type="submit" name="add_project" value="Add Project" class="btn">
      </form>
   </div>
</div>
</body>
</html>
