<?php
// Include the configuration file
@include 'config.php';

// Initialize message array
$message = [];

// Check if the form is submitted
if(isset($_POST['add_product'])){
    // Retrieve form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    error_reporting(E_ALL);
    ini_set('display_errors', 1);


    /*$image_folder = "/success_image";
        if (!file_exists($image_folder)) {
            mkdir($image_folder, 0777, true); // Create folder with full permissions
        }*/

    $image_folder = './success_image/'.$image;

    // Check if any field is empty
    if(empty($title) || empty($description) || empty($image)){
        $message[] = 'Please fill out all fields.';
    }else{
        // Limit description to 1000 characters
        $description = substr($description, 0, 1000);

        // Prepare the INSERT statement
        $insert = $conn->prepare("INSERT INTO success_stories (title, description, image) VALUES (?, ?, ?)");

        // Bind parameters to the prepared statement
        $insert->bind_param("sss", $title, $description, $image);

        // Execute the prepared statement
        if ($insert->execute()) {
            // Move uploaded image to the specified folder
            if(move_uploaded_file($image_tmp_name, $image_folder))
            {
                echo "Uploaded";
                // Redirect to success_dashboard.php
                header('Location: success_dashboard.php');
                exit(); // Stop further execution
            }
            else{
                echo "Not uploaded";
            }
        } else {
            $message[] = 'Could not add the product.';
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
   <title>Add Story</title>
   <link rel="stylesheet" href="add_new_story.css">
   <style>
    /* Set width and prevent resizing */
    #description {
        width: 500px; /* Adjust width as needed */
        resize: vertical; /* Allow vertical resizing only */
    }
   </style>
   <script>
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
   foreach($message as $message){
      // Display alert for each message
      echo "<script>alert('$message');</script>";
   }
}
?>

<div class="container">
   <div class="admin-product-form-container">
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
         <h3>Add a New Story</h3>
         <input type="text" placeholder="Enter Name" name="title" class="box">
         <span id="description_char_count" style="padding-left:10px; font-size: 12px; color: black;">1000 characters remaining</span>
         <textarea placeholder="Enter Story" id="description" name="description" class="box" oninput="countCharacters('description', 1000)"></textarea>
         <label>Enter Photo</label><br><br>
         <input type="file" accept="image/png, image/jpeg, image/jpg" name="image" class="box">
         <input type="submit" class="btn" name="add_product" value="Add Story">
      </form>
   </div>
</div>

</body>
</html>



