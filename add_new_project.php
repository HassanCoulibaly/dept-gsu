<?php
// Include database configuration
include 'config.php'; // Ensure this connects to 'cs_research_new' and has a $conn variable

// --- 1. Fetch Categories for the Dropdown List ---
$categories_from_db = []; // Initialize an empty array to store categories

// Create a new connection for fetching categories if 'config.php' doesn't provide one
// Or use the existing $conn from config.php if it's available.
// Assuming $conn is already established by config.php and points to 'cs_research_new'
if ($conn) {
    $sql_fetch_categories = "SELECT id, name FROM categories ORDER BY name ASC";
    $result_categories = mysqli_query($conn, $sql_fetch_categories);

    if ($result_categories) {
        while ($row_category = mysqli_fetch_assoc($result_categories)) {
            $categories_from_db[] = $row_category;
        }
    } else {
        // Handle error if categories cannot be fetched
        echo "Error fetching categories: " . mysqli_error($conn);
    }
} else {
    echo "Database connection not established in config.php!";
}


// Check if the form is submitted
if(isset($_POST['add_project'])) {
    // Retrieve form data
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $requirement = $_POST['requirement'];
    $keyword = $_POST['keyword'];
    $paid = $_POST['paid'];
    $category_id = $_POST['category']; // Renamed to category_id to reflect its purpose
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    $image_folder = './success_image/'.$image;

    // --- 2. Insert project data into the database ---
    // Ensure category_id is treated as an integer if that's its type in the DB
    $insert_query = "INSERT INTO projects (title, short_description, long_description, requirements, keywords, is_paid, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    if ($stmt) {
        // ssssssis - changed the 7th 's' to 'i' for integer category_id
        mysqli_stmt_bind_param($stmt, "ssssssis", $title, $short_description, $long_description, $requirement, $keyword, $paid, $category_id, $image);
        $upload = mysqli_stmt_execute($stmt); // This is line 27 (approx)

        if ($upload) {
            // If a new image is uploaded, move it to the specified folder
            if (!empty($image)) {
                move_uploaded_file($image_tmp_name, $image_folder);
            }
            // Redirect to project dashboard or show success message
            header('location: project_dashboard.php');
            exit(); // Make sure to exit after redirection
        } else {
            // Catch the specific foreign key error or other database errors
            echo "Error adding project: " . mysqli_error($conn);
            // Consider logging mysqli_error($conn) to a file for production
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Add Project</title>
   <link rel="stylesheet" href="add_new_project.css">
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

<div class="container">
   <div class="admin-product-form-container">
     <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
          <h3>Add a New Project</h3>

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Title</span> <br>
          <input type="text" placeholder="Enter Title" name="title" class="box">

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Short Description</span> <br>
          <span id="short_description_char_count" style="padding-left:10px; font-size: 12px; color: black;">500 characters remaining</span>
          <textarea placeholder="Enter Short Description" id="short_description" name="short_description" class="box" oninput="countCharacters('short_description', 500)"></textarea>

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Long Description</span> <br>
          <span id="long_description_word_count" style="padding-left:10px; font-size: 12px; color: black;">1000 words remaining</span>
          <textarea placeholder="Enter Long Description" id="long_description" name="long_description" class="box" oninput="countWords('long_description', 1000)"></textarea>

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Requirements</span> <br>
          <input type="text" placeholder="Enter Requirement" name="requirement" class="box">

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Keywords</span> <br>
          <input type="text" placeholder="Enter Keyword" name="keyword" class="box">

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Paid</span> <br>
          <select name="paid" class="box">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>

          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Category</span> <br>
          <select class="box" name="category">
            <?php
            // --- 3. Populate dropdown with fetched categories ---
            if (!empty($categories_from_db)) {
                foreach($categories_from_db as $cat) {
                    // Value should be the category ID, text displayed is the category name
                    echo "<option value='" . htmlspecialchars($cat['id']) . "'>" . htmlspecialchars($cat['name']) . "</option>";
                }
            } else {
                echo "<option value=''>No Categories Available</option>";
            }
            ?>
          </select>


          <span style="padding-left:10px; font-size: 12px; color: black; font-weight: bold;">Upload Image</span> <br>
          <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" class="box">
          <input type="submit" class="btn" name="add_project" value="Add Project">
      </form>
   </div>

</div>

</body>
</html>