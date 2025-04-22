<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@include 'config.php';
include 'config.php';

// Get the ID of the project to be updated
$id = isset($_GET['edit']) ? $_GET['edit'] : null;


// Initialize message array
$message = [];

// Define an array of categories

// Check if the form is submitted
if(isset($_POST['update_product'])){
    // Retrieve form data
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $requirement = $_POST['requirement']; // Modified input name
    $keyword = $_POST['keyword']; // Modified input name
    $paid = $_POST['paid']; // Modified input name
    $category = $_POST['category']; // Added category
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    // Check if any field is empty
    if(empty($title) || empty($short_description) || empty($long_description)){
        $message[] = 'Please fill out all required fields.';
    } else {
        // Check if a new image is uploaded
        if(!empty($image)) {
            $image_folder = './success_image/'.$image;
        } else {
            // Retrieve the existing image path from the database
            $select_image_query = "SELECT image FROM research WHERE id = ?";
            $stmt_select = mysqli_prepare($conn, $select_image_query);
            mysqli_stmt_bind_param($stmt_select, "i", $id);
            mysqli_stmt_execute($stmt_select);
            $result = mysqli_stmt_get_result($stmt_select);
            $row = mysqli_fetch_assoc($result);
            $image_folder = $row['image'];
        }

        // Update project data in the database
        $update_data_query = "UPDATE research SET title=?, short_description=?, long_description=?, requirement=?, keyword=?, paid=?, category=?, image=? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $update_data_query);
        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, "ssssssssi", $title, $short_description, $long_description, $requirement, $keyword, $paid, $category, $image_folder, $id); // Modified parameters
            $title = mysqli_real_escape_string($conn, $title);
            $short_description = mysqli_real_escape_string($conn, $short_description);
            $long_description = mysqli_real_escape_string($conn, $long_description);
            $requirement = mysqli_real_escape_string($conn, $requirement);
            $keyword = mysqli_real_escape_string($conn, $keyword);
            $paid = mysqli_real_escape_string($conn, $paid);
            $category = mysqli_real_escape_string($conn, $category); // Escape category value
            $image_folder = mysqli_real_escape_string($conn, $image_folder);
            $id = mysqli_real_escape_string($conn, $id);

            $upload = mysqli_stmt_execute($stmt_update);

            if($upload){
                // If a new image is uploaded, move it to the specified folder
                if(!empty($image)) {
                    move_uploaded_file($image_tmp_name, $image_folder);
                }
                header('location:project_dashboard.php');
            } else {
                $message[] = 'Could not update the project.';
            }

            mysqli_stmt_close($stmt_update);
        } else {
            $message[] = 'Could not prepare the statement.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project</title>
    <link rel="stylesheet" href="edit_project.css">
    <style>
        /* Set width and prevent resizing */
        #short_description, #long_description {
            width: 500px; /* Adjust width as needed */
            resize: vertical; /* Allow vertical resizing only */
        }
    </style>
    <script>
        function showAlert(message) {
            alert(message);
        }

        function countWords(textareaId, maxWords) {
            var textarea = document.getElementById(textareaId);
            var words = textarea.value.trim().split(/\s+/).length;

            // Limit remaining words to maxWords
            if (words > maxWords) {
                var trimmedText = textarea.value.trim().split(/\s+/).slice(0, maxWords).join(" ");
                textarea.value = trimmedText;
                words = maxWords; // Update words count
                alert("Maximum word limit reached");
            }

            // Update word count display
            var remainingWords = Math.max(0, maxWords - words);
            document.getElementById(textareaId + "_word_count").innerHTML = remainingWords + " words remaining";

            // Change color of word count based on remaining words
            document.getElementById(textareaId + "_word_count").style.color = remainingWords === 0 ? "red" : "black";
        }

        function countCharacters(textareaId, maxCharacters) {
            var textarea = document.getElementById(textareaId);
            var characters = textarea.value.length;

            // Limit remaining characters to maxCharacters
            if (characters > maxCharacters) {
                textarea.value = textarea.value.substring(0, maxCharacters);
                characters = maxCharacters; // Update characters count
                alert("Maximum character limit reached");
            }

            // Update character count display
            var remainingCharacters = Math.max(0, maxCharacters - characters);
            document.getElementById(textareaId + "_char_count").innerHTML = remainingCharacters + " characters remaining";

            // Change color of character count based on remaining characters
            document.getElementById(textareaId + "_char_count").style.color = remainingCharacters === 0 ? "red" : "black";
        }
    </script>
</head>
<body>

<?php
// Display messages if any
if(isset($message)){
    foreach($message as $msg){
        echo '<span class="message">'.$msg.'</span>';
    }
}
?>

<div class="container">

    <div class="admin-project-form-container centered">

        <?php
        // Retrieve project data based on the ID
        $select = mysqli_query($conn, "SELECT * FROM research WHERE id = '$id'");
        while($row = mysqli_fetch_assoc($select)){
            ?>

            <form action="" method="post" enctype="multipart/form-data">
                <h3 class="title">Update the Project</h3>

                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Title</span> <br>
                <input type="text" class="box" name="title" value="<?php echo $row['title']; ?>" placeholder="Enter the project title">

                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Short Description</span> <br>
                <span id="short_description_char_count" style="padding-left:10px; font-size: 12px; color: black;">500 characters remaining</span>
                <textarea type="text" class="box" id="short_description" name="short_description" oninput="countCharacters('short_description', 500)" placeholder="Enter the short description"><?php echo $row['short_description']; ?></textarea>

                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Long Description</span> <br>
                <span id="long_description_word_count" style="padding-left:10px; font-size: 12px; color: black;">1000 words remaining</span>
                <textarea class="box" name="long_description" id="long_description" placeholder="Enter the long description" oninput="countWords('long_description', 1000)"><?php echo $row['long_description']; ?></textarea>

                <!-- Display requirement, keyword, and paid columns -->
                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Requirements</span> <br>
                <input type="text" class="box" name="requirement" value="<?php echo $row['requirement']; ?>" placeholder="Enter the requirement">

                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Keywords</span> <br>
                <input type="text" class="box" name="keyword" value="<?php echo $row['keyword']; ?>" placeholder="Enter the keyword">

                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Pay</span> <br>
                <select class="box" name="paid">
                    <option value="yes" <?php if($row['paid'] == 'yes') echo 'selected'; ?>>Yes</option>
                    <option value="no" <?php if($row['paid'] == 'no') echo 'selected'; ?>>No</option>
                </select>

                <!-- Dropdown list for category -->
                <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Category</span> <br>
                <select class="box" name="category">
                    <?php
                    // Iterate over categories and generate options
                    foreach($categories as $category) {
                        // Check if the current category matches the one in the database
                        $selected = ($category == $row['category']) ? 'selected' : '';
                        echo "<option value='$category' $selected>$category</option>";
                    }
                    ?>
                </select>

                <!--<span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Update Photo</span> <br>-->
                <!--<input type="file" class="box" name="image" accept="image/png, image/jpeg, image/jpg">-->
                <input type="submit" value="Update Project" name="update_product" class="btn">
            </form>

        <?php }; ?>

    </div>

</div>

</body>
</html>


